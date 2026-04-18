<?php

namespace App\Http\Controllers;

use App\Mail\ServiceCompletedMail;
use App\Mail\ServiceInProgressMail;
use App\Models\CustomerNotification;
use App\Models\ServiceRecord;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Throwable;

class ShopCustomerManagementController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = Shop::firstOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_status' => 'not_activated',
                'expiry_date' => null,
                'description' => null,
                'rating' => 0,
            ]
        );

        $records = ServiceRecord::query()
            ->where('shop_id', $shop->shop_id)
            ->with([
                'customer:customer_id,user_id',
                'customer.user:id,name,phone',
            ])
            ->orderByDesc('service_id')
            ->get();

        return Inertia::render('Shop/CustomerManagement', [
            'records' => $records->map(function (ServiceRecord $record) {
                return [
                    'service_id' => $record->service_id,
                    'customer_name' => $record->customer?->user?->name ?? 'Customer',
                    'contact' => $record->phone_number ?: ($record->customer?->user?->phone ?? ''),
                    'phone_model' => $record->phone_model,
                    'phone_imei_number' => $record->phone_imei_number,
                    'customer_problem' => $record->customer_problem,
                    'shop_problem' => $record->shop_problem,
                    'repair_cost' => $record->repair_cost,
                    'status' => $record->status,
                    'date' => $record->created_at?->timezone(config('app.timezone'))->format('Y-m-d'),
                ];
            })->values(),
        ]);
    }

    public function updateProblem(Request $request, ServiceRecord $serviceRecord)
    {
        $this->assertShopOwnsRecord($request, $serviceRecord);

        $validated = $request->validate([
            'shop_problem' => ['required', 'string', 'max:5000'],
        ]);

        $serviceRecord->shop_problem = $validated['shop_problem'];
        $serviceRecord->save();

        return back();
    }

    public function updateCost(Request $request, ServiceRecord $serviceRecord)
    {
        $this->assertShopOwnsRecord($request, $serviceRecord);

        $validated = $request->validate([
            'repair_cost' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        $serviceRecord->repair_cost = round((float) $validated['repair_cost'], 2);
        $serviceRecord->save();

        return back();
    }

    public function updateStatus(Request $request, ServiceRecord $serviceRecord)
    {
        $this->assertShopOwnsRecord($request, $serviceRecord);

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject,start_progress,mark_complete,sent_from_shop'],
        ]);

        $targetStatus = $this->targetStatusForAction($serviceRecord->status, $validated['action']);
        if (! $targetStatus) {
            return back()->withErrors([
                'status' => 'This action is not allowed for the current status.',
            ]);
        }

        if ($targetStatus === 'completed' && (blank($serviceRecord->shop_problem) || $serviceRecord->repair_cost === null)) {
            return back()->withErrors([
                'status' => 'Set identified problem and repair cost before marking as completed.',
            ]);
        }

        $serviceRecord->status = $targetStatus;
        $this->queueCustomerStatusEmailIfNeeded($serviceRecord, $targetStatus);
        $serviceRecord->save();

        CustomerNotification::create([
            'customer_id' => $serviceRecord->customer_id,
            'service_id' => $serviceRecord->service_id,
            'message' => $this->notificationMessageForStatus($serviceRecord->phone_model, $targetStatus),
        ]);

        return back();
    }

    private function assertShopOwnsRecord(Request $request, ServiceRecord $serviceRecord): void
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = Shop::where('user_id', $user->id)->first();
        if (! $shop || (int) $serviceRecord->shop_id !== (int) $shop->shop_id) {
            abort(403);
        }
    }

    private function targetStatusForAction(string $currentStatus, string $action): ?string
    {
        return match ($action) {
            'accept' => $currentStatus === 'pending' ? 'accepted' : null,
            'reject' => $currentStatus === 'pending' ? 'rejected' : null,
            'start_progress' => $currentStatus === 'accepted' ? 'in progress' : null,
            'mark_complete' => $currentStatus === 'in progress' ? 'completed' : null,
            'sent_from_shop' => $currentStatus === 'completed' ? 'sent from shop' : null,
            default => null,
        };
    }

    private function notificationMessageForStatus(?string $phoneModel, string $status): string
    {
        $model = trim((string) $phoneModel) !== '' ? trim((string) $phoneModel) : 'your device';

        return match ($status) {
            'accepted' => "Your {$model} request has been accepted.",
            'rejected' => "Your {$model} request has been rejected.",
            'in progress' => "Your {$model} is now in progress.",
            'completed' => "Your {$model} repair is completed.",
            'sent from shop' => "Your {$model} has been sent from shop.",
            default => "Status updated for your {$model} request.",
        };
    }

    private function queueCustomerStatusEmailIfNeeded(ServiceRecord $serviceRecord, string $targetStatus): void
    {
        if (! in_array($targetStatus, ['in progress', 'completed'], true)) {
            return;
        }

        $serviceRecord->loadMissing('customer.user');
        $toEmail = $serviceRecord->customer?->user?->email;
        if (! $toEmail) {
            return;
        }

        if ($targetStatus === 'in progress' && $serviceRecord->in_progress_emailed_at === null) {
            try {
                Mail::to($toEmail)->queue(new ServiceInProgressMail($serviceRecord));
                $serviceRecord->in_progress_emailed_at = Carbon::now();
            } catch (Throwable $e) {
                Log::warning('Failed to queue in-progress service email.', [
                    'service_id' => $serviceRecord->service_id,
                    'to_email' => $toEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        if ($targetStatus === 'completed' && $serviceRecord->completed_emailed_at === null) {
            try {
                Mail::to($toEmail)->queue(new ServiceCompletedMail($serviceRecord));
                $serviceRecord->completed_emailed_at = Carbon::now();
            } catch (Throwable $e) {
                Log::warning('Failed to queue completed service email.', [
                    'service_id' => $serviceRecord->service_id,
                    'to_email' => $toEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
