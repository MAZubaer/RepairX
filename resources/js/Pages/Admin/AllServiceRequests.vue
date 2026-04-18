<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <AdminNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-7xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">All Service Requests</h1>
        <p class="mt-2 text-2xl text-gray-600">Monitor all repair requests across the platform</p>

        <section class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
          <article class="summary-box"><div class="summary-label">Total Requests</div><div class="summary-value">{{ summary.total }}</div></article>
          <article class="summary-box"><div class="summary-label">Pending</div><div class="summary-value text-amber-600">{{ summary.pending }}</div></article>
          <article class="summary-box"><div class="summary-label">In Progress</div><div class="summary-value text-yellow-600">{{ summary.in_progress }}</div></article>
          <article class="summary-box"><div class="summary-label">Sent from Shop</div><div class="summary-value text-blue-600">{{ summary.sent_from_shop }}</div></article>
          <article class="summary-box"><div class="summary-label">Delivered</div><div class="summary-value text-green-600">{{ summary.delivered }}</div></article>
        </section>

        <div class="mt-6">
          <div class="relative">
            <span class="search-icon">🔎</span>
            <input v-model="search" type="text" class="form-input search-input" placeholder="Search by customer, shop, phone model, or status..." />
          </div>
        </div>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1400px] text-left">
              <thead class="bg-slate-50 border-b border-slate-300">
                <tr class="text-sm text-slate-700">
                  <th class="px-4 py-4 font-semibold">Request ID</th>
                  <th class="px-4 py-4 font-semibold">Customer</th>
                  <th class="px-4 py-4 font-semibold">Shop</th>
                  <th class="px-4 py-4 font-semibold">Phone Model</th>
                  <th class="px-4 py-4 font-semibold">Customer Problem</th>
                  <th class="px-4 py-4 font-semibold">Identified Problem</th>
                  <th class="px-4 py-4 font-semibold">Cost</th>
                  <th class="px-4 py-4 font-semibold">Date</th>
                  <th class="px-4 py-4 font-semibold">Status</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="record in filteredRequests" :key="record.service_id" class="border-b border-slate-200 align-top">
                  <td class="px-4 py-4 text-xl font-semibold">#{{ record.service_id }}</td>
                  <td class="px-4 py-4 text-lg">
                    <div class="font-semibold">{{ record.customer_name }}</div>
                    <div class="text-slate-500">{{ record.customer_email }}</div>
                    <div class="text-slate-500">{{ record.customer_phone }}</div>
                  </td>
                  <td class="px-4 py-4 text-lg">{{ record.shop_name }}</td>
                  <td class="px-4 py-4 text-lg">{{ record.phone_model }}</td>
                  <td class="px-4 py-4 text-lg whitespace-pre-line">{{ record.customer_problem }}</td>
                  <td class="px-4 py-4 text-lg whitespace-pre-line">{{ record.shop_problem || 'Not provided yet' }}</td>
                  <td class="px-4 py-4 text-xl font-semibold">৳{{ formatAmount(record.cost) }}</td>
                  <td class="px-4 py-4 text-lg">{{ record.date }}</td>
                  <td class="px-4 py-4"><span :class="statusClass(record.status)">{{ statusLabel(record.status) }}</span></td>
                </tr>

                <tr v-if="!filteredRequests.length">
                  <td colspan="9" class="px-4 py-10 text-center text-slate-500">No service request found for this search.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import AdminNavbar from '../../Components/AdminNavbar.vue'

const props = defineProps({
  requests: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({ total: 0, pending: 0, in_progress: 0, sent_from_shop: 0, delivered: 0 }),
  },
})

const search = ref('')

const filteredRequests = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.requests

  return props.requests.filter((record) => {
    return [record.customer_name, record.shop_name, record.phone_model, record.status].some((v) =>
      String(v || '').toLowerCase().includes(q)
    )
  })
})

function formatAmount(amount) {
  const n = Number(amount)
  if (Number.isNaN(n)) return '0'
  return n.toLocaleString('en-US')
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
    'in progress': 'bg-yellow-100 text-yellow-700',
    completed: 'bg-purple-100 text-purple-700',
    'sent from shop': 'bg-blue-100 text-blue-700',
    delivered: 'bg-emerald-100 text-emerald-700',
  }

  return `${base} ${classes[status] || 'bg-slate-100 text-slate-700'}`
}
</script>

<style scoped>
.form-input {
  width: 100%;
  min-height: 2.9rem;
  padding: 0.6rem 0.85rem;
  border: 1px solid rgba(15, 23, 42, 0.16);
  border-radius: 0.7rem;
  background: #fff;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.08);
}

.search-icon {
  position: absolute;
  left: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.7;
}

.search-input {
  padding-left: 2.6rem;
}

.summary-box {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 0.9rem;
  background: #fff;
  padding: 1.05rem;
}

.summary-label {
  color: #475569;
}

.summary-value {
  font-size: 2rem;
  font-weight: 800;
  margin-top: 0.2rem;
}
</style>
