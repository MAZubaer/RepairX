<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <ShopNavbar />

    <main class="flex-1 py-10 px-4 sm:px-6">
      <div class="mx-auto w-full max-w-6xl">
        <section class="text-center">
          <p class="text-sm tracking-[0.2em] uppercase text-teal-700 font-semibold">RepairiX Billing</p>
          <h1 class="mt-2 text-4xl md:text-5xl font-black leading-tight">Choose your subscription plan</h1>
          <p class="mt-4 text-slate-600 max-w-2xl mx-auto">
            Your shop account needs an active plan to access dashboard, profile editing, and customer management.
          </p>
        </section>

        <section v-if="displayMessage" class="mt-6">
          <div class="rounded-xl border px-4 py-3 text-sm font-medium" :class="messageClass">
            {{ displayMessage }}
          </div>
        </section>

        <section v-if="errors.billing" class="mt-4">
          <div class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
            {{ errors.billing[0] }}
          </div>
        </section>

        <section class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <article class="plan-card">
            <div class="plan-top">
              <h2 class="text-2xl font-extrabold">Monthly</h2>
              <p class="mt-1 text-slate-600">Flexible plan for growing repair shops.</p>
              <div class="mt-5 flex items-end gap-2">
                <span class="text-5xl font-black">৳20</span>
                <span class="text-slate-500 mb-2">/month</span>
              </div>
            </div>

            <ul class="mt-6 space-y-2 text-sm text-slate-700">
              <li>Unlimited service requests</li>
              <li>Customer update workflow</li>
              <li>Status email notifications</li>
              <li>Priority support</li>
            </ul>

            <button
              type="button"
              class="btn-primary mt-8 w-full"
              :disabled="loadingPlan !== null"
              @click="startCheckout('monthly')"
            >
              {{ loadingPlan === 'monthly' ? 'Redirecting...' : 'Subscribe Monthly' }}
            </button>
          </article>

          <article class="plan-card featured">
            <div class="absolute right-4 top-4 rounded-full bg-dark text-white text-xs font-semibold px-3 py-1">Best Value</div>
            <div class="plan-top">
              <h2 class="text-2xl font-extrabold">Yearly</h2>
              <p class="mt-1 text-slate-600">Save more with annual billing.</p>
              <div class="mt-5 flex items-end gap-2">
                <span class="text-5xl font-black">৳180</span>
                <span class="text-slate-500 mb-2">/year</span>
              </div>
            </div>

            <ul class="mt-6 space-y-2 text-sm text-slate-700">
              <li>Everything in monthly plan</li>
              <li>2 months free equivalent</li>
              <li>Dedicated yearly billing support</li>
              <li>Best long-term value</li>
            </ul>

            <button
              type="button"
              class="btn-primary mt-8 w-full"
              :disabled="loadingPlan !== null"
              @click="startCheckout('yearly')"
            >
              {{ loadingPlan === 'yearly' ? 'Redirecting...' : 'Subscribe Yearly' }}
            </button>
          </article>
        </section>

        <section v-if="isActive" class="mt-8 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-4 text-emerald-800 text-sm">
          Active subscription detected. You can continue to your dashboard.
          <a href="/dashboard/shop" class="underline font-semibold ml-1">Go to Dashboard</a>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/inertia-vue3'
import ShopNavbar from '../../Components/ShopNavbar.vue'

const props = defineProps({
  shop: {
    type: Object,
    required: true,
  },
  stripe: {
    type: Object,
    required: true,
  },
  message: {
    type: String,
    default: null,
  },
})

const page = usePage()
const errors = computed(() => page.props.value.errors || {})
const loadingPlan = ref(null)

const isActive = computed(() => {
  if (props.shop.subscription_status !== 'activated' || !props.shop.expiry_date) {
    return false
  }

  return new Date(props.shop.expiry_date).getTime() > Date.now()
})

const displayMessage = computed(() => {
  if (props.message === 'checkout_cancelled') {
    return 'Checkout was cancelled. Please choose a plan to activate your shop account.'
  }

  if (props.message === 'payment_processing') {
    return 'Payment received. Your subscription is being confirmed, please refresh in a few seconds.'
  }

  return null
})

const messageClass = computed(() => {
  if (props.message === 'payment_processing') {
    return 'border-emerald-300 bg-emerald-50 text-emerald-700'
  }

  return 'border-amber-300 bg-amber-50 text-amber-700'
})

function startCheckout(plan) {
  loadingPlan.value = plan

  Inertia.post('/shop/billing/checkout', { plan }, {
    onFinish: () => {
      loadingPlan.value = null
    },
  })
}
</script>

<style scoped>
.plan-card {
  position: relative;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 1rem;
  background: linear-gradient(145deg, #ffffff, #f7fbfc);
  padding: 1.5rem;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.plan-card.featured {
  border-color: rgba(20, 184, 166, 0.45);
  background: linear-gradient(145deg, #e9fffb, #ffffff);
}

.plan-top {
  border-bottom: 1px dashed rgba(15, 23, 42, 0.16);
  padding-bottom: 1rem;
}
</style>
