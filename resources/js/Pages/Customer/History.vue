<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <CustomerNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Request History</h1>
        <p class="mt-2 text-slate-600 text-lg">Completed and delivered repair requests</p>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left">
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
                    {{ record.shop_problem || 'Not provided' }}
                  </td>
                  <td class="px-4 py-4 text-lg">{{ displayCost(record.repair_cost) }}</td>
                  <td class="px-4 py-4 text-lg">{{ record.date }}</td>
                  <td class="px-4 py-4">
                    <span :class="statusClass(record.status)">{{ statusLabel(record.status) }}</span>
                  </td>
                </tr>

                <tr v-if="!records.length">
                  <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                    No completed or delivered requests yet.
                  </td>
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
import CustomerNavbar from '../../Components/CustomerNavbar.vue'

defineProps({
  records: { type: Array, default: () => [] },
})

function displayCost(cost) {
  if (cost === null || cost === undefined || cost === '') return 'Not yet quoted'
  const value = Number(cost)
  if (Number.isNaN(value)) return String(cost)
  return `৳${value}`
}

function statusLabel(status) {
  const map = {
    completed: 'Completed',
    delivered: 'Delivered',
  }

  return map[status] || status
}

function statusClass(status) {
  const base = 'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold'

  if (status === 'completed') {
    return `${base} bg-purple-100 text-purple-700`
  }

  if (status === 'delivered') {
    return `${base} bg-slate-200 text-slate-700`
  }

  return `${base} bg-slate-100 text-slate-700`
}
</script>

<style scoped>
</style>
