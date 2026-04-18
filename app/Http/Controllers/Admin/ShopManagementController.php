<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopManagementController extends Controller
{
    public function index()
    {
        $shops = Shop::query()
            ->with('user:id,name,email,phone,location')
            ->orderByDesc('shop_id')
            ->get();

        $mappedShops = $shops->map(function (Shop $shop) {
            return [
                'shop_id' => $shop->shop_id,
                'name' => $shop->user?->name ?? 'Shop',
                'owner' => $shop->user?->name ?? 'Owner',
                'contact_email' => $shop->user?->email,
                'contact_phone' => $shop->user?->phone,
                'location' => $shop->user?->location,
                'status' => $shop->subscription_status,
                'plan' => $shop->subscription_plan ?: 'monthly',
                'expiry_date' => $shop->expiry_date?->format('Y-m-d'),
            ];
        })->values();

        return Inertia::render('Admin/ShopManagement', [
            'shops' => $mappedShops,
            'summary' => [
                'total' => $shops->count(),
                'active' => $shops->where('subscription_status', 'activated')->count(),
                'inactive' => $shops->where('subscription_status', 'not_activated')->count(),
            ],
        ]);
    }

    public function toggleStatus(Shop $shop)
    {
        $newStatus = $shop->subscription_status === 'activated' ? 'not_activated' : 'activated';

        $shop->subscription_status = $newStatus;

        if ($newStatus === 'not_activated') {
            $shop->expiry_date = null;
        } elseif (! $shop->expiry_date) {
            $shop->expiry_date = \Illuminate\Support\Carbon::now()->addMonth();
        }

        $shop->save();

        return back();
    }

    public function updatePlan(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'plan' => ['required', 'in:monthly,yearly'],
        ]);

        $shop->subscription_plan = $validated['plan'];
        $shop->save();

        return back();
    }

    public function updateExpiry(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'expiry_date' => ['required', 'date'],
        ]);

        $shop->expiry_date = $validated['expiry_date'];
        $shop->save();

        return back();
    }
}
