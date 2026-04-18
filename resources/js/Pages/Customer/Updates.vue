<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <CustomerNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">My Repair Requests</h1>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1280px] text-left">
              <thead class="bg-slate-50 border-b border-slate-300">
                <tr class="text-sm text-slate-700">
                  <th class="px-4 py-4 font-semibold">Customer Phone</th>
                  <th class="px-4 py-4 font-semibold">Phone Model</th>
                  <th class="px-4 py-4 font-semibold">Contact</th>
                  <th class="px-4 py-4 font-semibold">Customer Problem</th>
                  <th class="px-4 py-4 font-semibold">Identified Problem</th>
                  <th class="px-4 py-4 font-semibold">Cost</th>
                  <th class="px-4 py-4 font-semibold">Date</th>
                  <th class="px-4 py-4 font-semibold">Status</th>
                  <th class="px-4 py-4 font-semibold">Actions</th>
                  <th class="px-4 py-4 font-semibold">Rating</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="record in records"
                  :key="record.service_id"
                  class="border-b border-slate-200 align-top"
                >
                  <td class="px-4 py-4">
                    <div class="font-bold text-2xl leading-tight">{{ record.customer_phone }}</div>
                    <div class="text-sm text-slate-500">IMEI: {{ record.phone_imei_number }}</div>
                  </td>

                  <td class="px-4 py-4 text-2xl leading-tight">{{ record.phone_model }}</td>
                  <td class="px-4 py-4 text-lg">{{ record.contact }}</td>
                  <td class="px-4 py-4 text-base whitespace-pre-line">{{ record.customer_problem }}</td>

                  <td class="px-4 py-4 text-base whitespace-pre-line text-slate-700">
                    {{ record.shop_problem || 'Not yet diagnosed' }}
                  </td>

                  <td class="px-4 py-4 text-lg">
                    {{ displayCost(record.repair_cost) }}
                  </td>

                  <td class="px-4 py-4 text-lg">{{ record.date }}</td>

                  <td class="px-4 py-4">
                    <div class="space-y-1">
                      <span :class="statusClass(record.status)">{{ statusLabel(record.status) }}</span>
                      <p class="text-xs text-slate-500 max-w-[210px]">{{ statusMessage(record) }}</p>
                    </div>
                  </td>

                  <td class="px-4 py-4">
                    <button
                      v-if="record.status === 'sent from shop'"
                      type="button"
                      class="btn-accept"
                      @click="acceptDelivery(record)"
                    >
                      Accept
                    </button>
                    <span v-else class="text-slate-500 text-sm">No actions</span>
                  </td>

                  <td class="px-4 py-4">
                    <div v-if="record.status === 'delivered'" class="space-y-2 min-w-[180px]">
                      <div v-if="record.rating" class="text-sm font-semibold text-slate-700">
                        {{ renderStars(record.rating) }} ({{ record.rating }}/5)
                      </div>

                      <div class="flex items-center gap-2">
                        <select v-model="ratingDrafts[record.service_id]" class="rating-select">
                          <option value="">Rate</option>
                          <option value="1">1</option>
                          <option value="2">2</option>
                          <option value="3">3</option>
                          <option value="4">4</option>
                          <option value="5">5</option>
                        </select>
                        <button
                          type="button"
                          class="btn-rate"
                          :disabled="!ratingDrafts[record.service_id]"
                          @click="submitRating(record)"
                        >
                          {{ record.rating ? 'Update' : 'Rate' }}
                        </button>
                      </div>
                    </div>
                    <span v-else class="text-slate-500 text-sm">-</span>
                  </td>
                </tr>

                <tr v-if="!records.length">
                  <td colspan="10" class="px-4 py-10 text-center text-slate-500">
                    No repair requests yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <p v-if="errors.status" class="mt-3 text-red-600 text-sm">{{ errors.status[0] }}</p>
        <p v-if="errors.rating" class="mt-2 text-red-600 text-sm">{{ errors.rating[0] }}</p>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/inertia-vue3'
import CustomerNavbar from '../../Components/CustomerNavbar.vue'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.value.errors || {})
const ratingDrafts = reactive({})

watch(
  () => props.records,
  (records) => {
    Object.keys(ratingDrafts).forEach((k) => delete ratingDrafts[k])
    ;(records || []).forEach((record) => {
      ratingDrafts[record.service_id] = record.rating ? String(record.rating) : ''
    })
  },
  { immediate: true }
)

function acceptDelivery(record) {
  Inertia.post(`/customer/updates/${record.service_id}/accept`, {}, { preserveScroll: true })
}

function submitRating(record) {
  Inertia.post(
    `/customer/updates/${record.service_id}/rating`,
    { rating: Number(ratingDrafts[record.service_id] || 0) },
    { preserveScroll: true }
  )
}

function displayCost(cost) {
  if (cost === null || cost === undefined || cost === '') return 'Not yet quoted'
  const value = Number(cost)
  if (Number.isNaN(value)) return String(cost)
  return `৳${value}`
}

function statusLabel(status) {
  const map = {
    pending: 'Pending',
    accepted: 'Accepted',
    rejected: 'Rejected',
    'in progress': 'In Progress',
    completed: 'Completed',
    'sent from shop': 'Sent from Shop',
    delivered: 'Delivered',
  }

  return map[status] || status
}

function statusClass(status) {
  const base = 'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold'

  const classes = {
    pending: 'bg-amber-100 text-amber-700',
    accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-rose-100 text-rose-700',
    'in progress': 'bg-blue-100 text-blue-700',
    completed: 'bg-purple-100 text-purple-700',
    'sent from shop': 'bg-teal-100 text-teal-700',
    delivered: 'bg-slate-200 text-slate-700',
  }

  return `${base} ${classes[status] || 'bg-slate-100 text-slate-700'}`
}

function statusMessage(record) {
  const model = record.phone_model || 'device'
  const shopName = record.shop_name || 'The shop'

  if (record.status === 'pending') return `${shopName} received your ${model} request.`
  if (record.status === 'accepted') return `Your ${model} request has been accepted.`
  if (record.status === 'rejected') return `Your ${model} request has been rejected.`
  if (record.status === 'in progress') return `Repair work for your ${model} is in progress.`
  if (record.status === 'completed') return `Repair work for your ${model} is completed.`
  if (record.status === 'sent from shop') return `Your ${model} has been sent from shop. Please accept delivery.`
  if (record.status === 'delivered' && !record.rating) return `Delivery confirmed. Please rate your experience.`
  if (record.status === 'delivered' && record.rating) return `Thanks for rating your ${model} service.`

  return `Status updated for your ${model} request.`
}

function renderStars(rating) {
  const value = Number(rating || 0)
  const normalized = Math.max(0, Math.min(5, value))
  return '★'.repeat(normalized) + '☆'.repeat(5 - normalized)
}
</script>

<style scoped>
.btn-accept {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.65rem;
  padding: 0.52rem 0.95rem;
  background: #22c55e;
  color: #fff;
  font-weight: 700;
}
.btn-accept:hover {
  opacity: 0.92;
}
.rating-select {
  min-height: 2.1rem;
  border: 1px solid rgba(15, 23, 42, 0.2);
  border-radius: 0.5rem;
  padding: 0.35rem 0.5rem;
  background: #fff;
}
.btn-rate {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  padding: 0.35rem 0.65rem;
  background: #0ea5e9;
  color: #fff;
  font-weight: 700;
  font-size: 0.78rem;
}
.btn-rate:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-rate:hover:not(:disabled) {
  opacity: 0.92;
}
</style>
