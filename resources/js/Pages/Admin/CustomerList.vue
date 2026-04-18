<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <AdminNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-7xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Customer List</h1>
        <p class="mt-2 text-2xl text-gray-600">Manage all registered customers on the platform</p>

        <section class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <article class="summary-box"><div class="summary-label">Total Customers</div><div class="summary-value">{{ summary.total_customers }}</div></article>
          <article class="summary-box"><div class="summary-label">Total Requests</div><div class="summary-value text-teal-600">{{ summary.total_requests }}</div></article>
          <article class="summary-box"><div class="summary-label">Active Requests</div><div class="summary-value text-amber-600">{{ summary.active_requests }}</div></article>
          <article class="summary-box"><div class="summary-label">Completed Requests</div><div class="summary-value text-green-600">{{ summary.completed_requests }}</div></article>
        </section>

        <div class="mt-6">
          <div class="relative">
            <span class="search-icon">🔎</span>
            <input v-model="search" type="text" class="form-input search-input" placeholder="Search by name, email, phone, or address..." />
          </div>
        </div>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1300px] text-left">
              <thead class="bg-slate-50 border-b border-slate-300">
                <tr class="text-sm text-slate-700">
                  <th class="px-4 py-4 font-semibold">Customer ID</th>
                  <th class="px-4 py-4 font-semibold">Name</th>
                  <th class="px-4 py-4 font-semibold">Email</th>
                  <th class="px-4 py-4 font-semibold">Phone</th>
                  <th class="px-4 py-4 font-semibold">Address</th>
                  <th class="px-4 py-4 font-semibold">Join Date</th>
                  <th class="px-4 py-4 font-semibold">Total Requests</th>
                  <th class="px-4 py-4 font-semibold">Active</th>
                  <th class="px-4 py-4 font-semibold">Completed</th>
                  <th class="px-4 py-4 font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="customer in filteredCustomers" :key="customer.customer_id" class="border-b border-slate-200">
                  <td class="px-4 py-4 text-xl font-semibold">#C{{ String(customer.customer_id).padStart(4, '0') }}</td>
                  <td class="px-4 py-4 text-2xl font-semibold">{{ customer.name }}</td>
                  <td class="px-4 py-4 text-lg">{{ customer.email }}</td>
                  <td class="px-4 py-4 text-lg">{{ customer.phone }}</td>
                  <td class="px-4 py-4 text-lg">{{ customer.address }}</td>
                  <td class="px-4 py-4 text-lg">{{ customer.join_date }}</td>
                  <td class="px-4 py-4 text-xl">{{ customer.total_requests }}</td>
                  <td class="px-4 py-4 text-xl text-amber-600">{{ customer.active_requests }}</td>
                  <td class="px-4 py-4 text-xl text-green-600">{{ customer.completed_requests }}</td>
                  <td class="px-4 py-4">
                    <button type="button" class="btn-delete" @click="deleteCustomer(customer)">Delete</button>
                  </td>
                </tr>

                <tr v-if="!filteredCustomers.length">
                  <td colspan="10" class="px-4 py-10 text-center text-slate-500">No customer found for this search.</td>
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
import { Inertia } from '@inertiajs/inertia'
import AdminNavbar from '../../Components/AdminNavbar.vue'

const props = defineProps({
  customers: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({ total_customers: 0, total_requests: 0, active_requests: 0, completed_requests: 0 }),
  },
})

const search = ref('')

const filteredCustomers = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.customers

  return props.customers.filter((customer) => {
    return [customer.name, customer.email, customer.phone, customer.address].some((v) =>
      String(v || '').toLowerCase().includes(q)
    )
  })
})

function deleteCustomer(customer) {
  const ok = confirm(`Delete ${customer.name}? This will remove customer data from database.`)
  if (!ok) return

  Inertia.delete(`/admin/customers/${customer.customer_id}`, { preserveScroll: true })
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

.btn-delete {
  border-radius: 0.7rem;
  padding: 0.5rem 0.95rem;
  font-weight: 700;
  background: #fee2e2;
  color: #dc2626;
}
</style>
