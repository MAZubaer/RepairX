<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\ShopProfileController;
use App\Http\Controllers\ShopDashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerShopController;
use App\Http\Controllers\ShopCustomerManagementController;
use App\Http\Controllers\CustomerUpdatesController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\ShopBillingController;
use App\Models\Shop;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/about', function () {
    $user = Auth::user();

    return Inertia::render('About', [
        'role' => $user?->role,
    ]);
})->name('about');

// Registration pages
use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Auth\ShopRegisterController;

Route::get('/register/customer', [CustomerRegisterController::class, 'create'])->name('register.customer');
Route::post('/register/customer', [CustomerRegisterController::class, 'store'])->name('register.customer.store');

Route::get('/register/shop', [ShopRegisterController::class, 'create'])->name('register.shop');
Route::post('/register/shop', [ShopRegisterController::class, 'store'])->name('register.shop.store');

// (temporary CSRF-exempt test endpoints removed)

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role === 'shop' ? 'dashboard.shop' : 'dashboard.customer');
    }

    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (! Auth::attempt($credentials)) {
        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    $request->session()->regenerate();

    $user = Auth::user();
    if ($user->role === 'shop') {
        $shop = Shop::where('user_id', $user->id)->first();

        $isSubscriptionActive = $shop
            && $shop->subscription_status === 'activated'
            && $shop->expiry_date
            && $shop->expiry_date->isFuture();

        if (! $isSubscriptionActive) {
            return redirect()->route('shop.pricing');
        }

        return redirect()->route('dashboard.shop');
    }

    return redirect()->route('dashboard.customer');
})->name('login.store');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

// Dashboards
Route::get('/dashboard/customer', function () {
    return app(CustomerDashboardController::class)->show();
})->name('dashboard.customer')->middleware('auth');

Route::get('/customer/shops/{shop}', [CustomerShopController::class, 'show'])
    ->name('customer.shops.show')
    ->middleware('auth');

Route::post('/customer/shops/{shop}/requests', [CustomerShopController::class, 'storeRequest'])
    ->name('customer.shops.requests.store')
    ->middleware('auth');

Route::get('/update', [CustomerUpdatesController::class, 'show'])
    ->name('customer.updates')
    ->middleware('auth');

Route::get('/history', [CustomerUpdatesController::class, 'showHistory'])
    ->name('customer.history')
    ->middleware('auth');

Route::get('/notifications', [CustomerNotificationController::class, 'show'])
    ->name('customer.notifications')
    ->middleware('auth');

Route::post('/customer/updates/{serviceRecord}/accept', [CustomerUpdatesController::class, 'acceptDelivery'])
    ->name('customer.updates.accept')
    ->middleware('auth');

Route::post('/customer/updates/{serviceRecord}/rating', [CustomerUpdatesController::class, 'submitRating'])
    ->name('customer.updates.rating')
    ->middleware('auth');

Route::get('/dashboard/shop', function () {
    return app(ShopDashboardController::class)->show();
})->name('dashboard.shop')->middleware(['auth', 'shop.subscription']);

Route::get('/shop/pricing', [ShopBillingController::class, 'pricing'])
    ->name('shop.pricing')
    ->middleware('auth');

Route::post('/shop/billing/checkout', [ShopBillingController::class, 'createCheckoutSession'])
    ->name('shop.billing.checkout')
    ->middleware('auth');

Route::get('/shop/billing/success', [ShopBillingController::class, 'success'])
    ->name('shop.billing.success')
    ->middleware('auth');

Route::post('/stripe/webhook', [ShopBillingController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

Route::get('/stripe/webhook', function () {
    return response()->json([
        'message' => 'Stripe webhook endpoint is active. Send POST requests from Stripe.',
    ]);
});

Route::get('/shop/customers', [ShopCustomerManagementController::class, 'show'])
    ->name('shop.customers.index')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/problem', [ShopCustomerManagementController::class, 'updateProblem'])
    ->name('shop.customers.problem.update')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/cost', [ShopCustomerManagementController::class, 'updateCost'])
    ->name('shop.customers.cost.update')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/status', [ShopCustomerManagementController::class, 'updateStatus'])
    ->name('shop.customers.status.update')
    ->middleware(['auth', 'shop.subscription']);

Route::get('/shop/edit', [ShopProfileController::class, 'edit'])
    ->name('shop.edit')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/edit', [ShopProfileController::class, 'update'])
    ->name('shop.update')
    ->middleware(['auth', 'shop.subscription']);

Route::get('/shop/images/{shopImage}', [ShopProfileController::class, 'showImage'])
    ->name('shop.images.show')
    ->middleware('auth');

Route::delete('/shop/images/{shopImage}', [ShopProfileController::class, 'destroyImage'])
    ->name('shop.images.destroy')
    ->middleware(['auth', 'shop.subscription']);

// (debug route removed)
