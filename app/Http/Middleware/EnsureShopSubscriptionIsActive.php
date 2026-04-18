<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
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
                'description' => $user->location,
                'rating' => 0,
            ]
        );

        $expiryDate = $shop->expiry_date;
        $isActive = $shop->subscription_status === 'activated'
            && $expiryDate !== null
            && Carbon::parse($expiryDate)->isFuture();

        if (! $isActive) {
            if ($shop->subscription_status !== 'not_activated') {
                $shop->update(['subscription_status' => 'not_activated']);
            }

            return redirect()->route('shop.pricing');
        }

        return $next($request);
    }
}
