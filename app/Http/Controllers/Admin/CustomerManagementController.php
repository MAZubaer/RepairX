<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ServiceRecord;
use Inertia\Inertia;

class CustomerManagementController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->with('user:id,name,email,phone,location')
            ->orderByDesc('customer_id')
            ->get();

        $serviceCounts = ServiceRecord::query()
            ->selectRaw('customer_id, COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN status IN ('pending','accepted','in progress') THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN status IN ('completed','sent from shop','delivered') THEN 1 ELSE 0 END) as completed_count")
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $mapped = $customers->map(function (Customer $customer) use ($serviceCounts) {
            $counts = $serviceCounts->get($customer->customer_id);

            return [
                'customer_id' => $customer->customer_id,
                'name' => $customer->user?->name,
                'email' => $customer->user?->email,
                'phone' => $customer->user?->phone,
                'address' => $customer->user?->location,
                'join_date' => $customer->created_at?->timezone(config('app.timezone'))->format('Y-m-d'),
                'total_requests' => (int) ($counts->total_count ?? 0),
                'active_requests' => (int) ($counts->active_count ?? 0),
                'completed_requests' => (int) ($counts->completed_count ?? 0),
                'user_id' => $customer->user_id,
            ];
        })->values();

        return Inertia::render('Admin/CustomerList', [
            'customers' => $mapped,
            'summary' => [
                'total_customers' => $customers->count(),
                'total_requests' => (int) ServiceRecord::count(),
                'active_requests' => (int) ServiceRecord::whereIn('status', ['pending', 'accepted', 'in progress'])->count(),
                'completed_requests' => (int) ServiceRecord::whereIn('status', ['completed', 'sent from shop', 'delivered'])->count(),
            ],
        ]);
    }

    public function destroy(Customer $customer)
    {
        $user = $customer->user;

        if ($user) {
            $user->delete();
        } else {
            $customer->delete();
        }

        return back();
    }
}
