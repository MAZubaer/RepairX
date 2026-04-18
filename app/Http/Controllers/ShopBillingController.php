<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;
use UnexpectedValueException;

class ShopBillingController extends Controller
{
    public function pricing(Request $request)
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

        return Inertia::render('Shop/Pricing', [
            'shop' => [
                'subscription_status' => $shop->subscription_status,
                'subscription_plan' => $shop->subscription_plan,
                'expiry_date' => optional($shop->expiry_date)->toIso8601String(),
                'current_period_end' => optional($shop->current_period_end)->toIso8601String(),
            ],
            'stripe' => [
                'publishable_key' => config('services.stripe.key'),
            ],
            'message' => $request->query('message'),
        ]);
    }

    public function createCheckoutSession(Request $request)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $validated = $request->validate([
            'plan' => 'required|in:monthly,yearly',
        ]);

        $plan = $validated['plan'];
        $priceId = $plan === 'monthly'
            ? config('services.stripe.monthly_price_id')
            : config('services.stripe.yearly_price_id');

        if (! $priceId) {
            return back()->withErrors([
                'billing' => 'Stripe price id is not configured for the selected plan.',
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $shop = Shop::firstOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_status' => 'not_activated',
                'expiry_date' => null,
                'description' => $user->location,
                'rating' => 0,
            ]
        );

        if (! $shop->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'shop_id' => (string) $shop->shop_id,
                ],
            ]);

            $shop->update([
                'stripe_customer_id' => $customer->id,
            ]);
        }

        $checkoutSession = Session::create([
            'mode' => 'subscription',
            'customer' => $shop->stripe_customer_id,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => route('shop.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('shop.pricing', ['message' => 'checkout_cancelled']),
            'client_reference_id' => (string) $shop->shop_id,
            'metadata' => [
                'shop_id' => (string) $shop->shop_id,
                'user_id' => (string) $user->id,
                'plan' => $plan,
            ],
        ]);

        return Inertia::location($checkoutSession->url);
    }

    public function success(Request $request)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = Shop::where('user_id', $user->id)->first();
        if ($shop && $shop->subscription_status === 'activated' && $shop->expiry_date && $shop->expiry_date->isFuture()) {
            return redirect()->route('dashboard.shop');
        }

        return redirect()->route('shop.pricing', [
            'message' => 'payment_processing',
        ]);
    }

    public function webhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            if (! $webhookSecret) {
                throw new UnexpectedValueException('Missing Stripe webhook secret.');
            }

            $event = Webhook::constructEvent($payload, (string) $signature, $webhookSecret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook verification failed: '.$e->getMessage());
            return response('Invalid webhook signature.', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpsert($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            default:
                // Ignore unhandled events.
                break;
        }

        return response('ok', 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $shopId = $session->metadata->shop_id ?? $session->client_reference_id ?? null;
        $subscriptionId = $session->subscription ?? null;

        if (! $shopId || ! $subscriptionId) {
            return;
        }

        $shop = Shop::where('shop_id', (int) $shopId)->first();
        if (! $shop) {
            return;
        }

        if (! $shop->stripe_subscription_id || $shop->stripe_subscription_id !== $subscriptionId) {
            $shop->update([
                'stripe_subscription_id' => $subscriptionId,
            ]);
        }

        $subscription = Subscription::retrieve($subscriptionId);
        $this->syncSubscriptionToShop($shop, $subscription);
    }

    private function handleSubscriptionUpsert(object $subscription): void
    {
        $shop = Shop::where('stripe_subscription_id', $subscription->id)->first();

        if (! $shop && isset($subscription->metadata->shop_id)) {
            $shop = Shop::where('shop_id', (int) $subscription->metadata->shop_id)->first();
        }

        if (! $shop && isset($subscription->customer)) {
            $shop = Shop::where('stripe_customer_id', $subscription->customer)->first();
        }

        if (! $shop) {
            return;
        }

        $this->syncSubscriptionToShop($shop, $subscription);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $shop = Shop::where('stripe_subscription_id', $subscription->id)
            ->orWhere('stripe_customer_id', $subscription->customer ?? '')
            ->first();

        if (! $shop) {
            return;
        }

        $shop->update([
            'subscription_status' => 'not_activated',
            'subscription_plan' => null,
            'expiry_date' => Carbon::now(),
            'current_period_end' => Carbon::now(),
            'stripe_subscription_id' => $subscription->id,
        ]);
    }

    private function syncSubscriptionToShop(Shop $shop, object $subscription): void
    {
        $stripeStatus = (string) ($subscription->status ?? '');
        $isActive = in_array($stripeStatus, ['active', 'trialing'], true);

        $currentPeriodEnd = null;
        if (isset($subscription->current_period_end) && is_numeric($subscription->current_period_end)) {
            $currentPeriodEnd = Carbon::createFromTimestamp((int) $subscription->current_period_end);
        }

        $priceId = $subscription->items->data[0]->price->id ?? null;
        $plan = null;

        if ($priceId && $priceId === config('services.stripe.monthly_price_id')) {
            $plan = 'monthly';
        } elseif ($priceId && $priceId === config('services.stripe.yearly_price_id')) {
            $plan = 'yearly';
        }

        $shop->update([
            'stripe_customer_id' => $subscription->customer ?? $shop->stripe_customer_id,
            'stripe_subscription_id' => $subscription->id,
            'subscription_status' => $isActive ? 'activated' : 'not_activated',
            'subscription_plan' => $plan,
            'expiry_date' => $currentPeriodEnd,
            'current_period_end' => $currentPeriodEnd,
        ]);
    }
}
