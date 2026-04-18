<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShopRegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('RegisterShop');
    }

    public function store(RegisterShopRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['shop_name'],
                    'email' => $validated['email'],
                    'location' => $validated['shop_address'],
                    'phone' => $validated['phone'],
                    'role' => 'shop',
                    // Password is hashed by User model cast.
                    'password' => $validated['password'],
                ]);

                Shop::create([
                    'user_id' => $user->id,
                    'subscription_status' => 'not_activated',
                    'expiry_date' => null,
                    'description' => $validated['shop_address'],
                    'rating' => 0,
                ]);

                return $user;
            });

            // Log the user in and redirect to pricing for subscription activation.
            Auth::login($user);
            return redirect()->route('shop.pricing');
        } catch (\Exception $e) {
            \Log::error('Shop registration failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['server' => 'Unable to create shop account. ' . $e->getMessage()]);
        }
    }
}
