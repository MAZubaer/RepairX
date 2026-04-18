<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRecord;
use Inertia\Inertia;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $records = ServiceRecord::query()
            ->with([
                'customer:customer_id,user_id',
                'customer.user:id,name,email,phone',
                'shop:shop_id,user_id',
                'shop.user:id,name',
            ])
            ->orderByDesc('service_id')
            ->get();

        $mapped = $records->map(function (ServiceRecord $record) {
            return [
                'service_id' => $record->service_id,
                'customer_name' => $record->customer?->user?->name ?? 'Customer',
                'customer_email' => $record->customer?->user?->email,
                'customer_phone' => $record->phone_number ?: ($record->customer?->user?->phone ?? ''),
                'shop_name' => $record->shop?->user?->name ?? 'Shop',
                'phone_model' => $record->phone_model,
                'customer_problem' => $record->customer_problem,
                'shop_problem' => $record->shop_problem,
                'cost' => $record->repair_cost,
                'status' => $record->status,
                'date' => $record->created_at?->timezone(config('app.timezone'))->format('Y-m-d'),
            ];
        })->values();

        return Inertia::render('Admin/AllServiceRequests', [
            'requests' => $mapped,
            'summary' => [
                'total' => $records->count(),
                'pending' => $records->where('status', 'pending')->count(),
                'in_progress' => $records->where('status', 'in progress')->count(),
                'sent_from_shop' => $records->where('status', 'sent from shop')->count(),
                'delivered' => $records->where('status', 'delivered')->count(),
            ],
        ]);
    }
}
