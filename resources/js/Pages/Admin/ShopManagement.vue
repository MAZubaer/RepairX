<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <AdminNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-7xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Shop Management</h1>
        <p class="mt-2 text-2xl text-gray-600">Manage shop subscriptions, plans, and activation status</p>

        <section class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <article class="summary-box">
            <div class="summary-label">Total Shops</div>
            <div class="summary-value">{{ summary.total }}</div>
          </article>
          <article class="summary-box">
            <div class="summary-label">Active Subscriptions</div>
            <div class="summary-value text-green-600">{{ summary.active }}</div>
          </article>
          <article class="summary-box">
            <div class="summary-label">Inactive Subscriptions</div>
            <div class="summary-value text-red-600">{{ summary.inactive }}</div>
          </article>
        </section>

        <div class="mt-6">
          <div class="relative">
            <span class="search-icon">🔎</span>
            <input v-model="search" type="text" class="form-input search-input" placeholder="Search shops by name, owner, or location..." />
          </div>
        </div>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left">
              <thead class="bg-slate-50 border-b border-slate-300">
                <tr class="text-sm text-slate-700">
                  <th class="px-4 py-4 font-semibold">Shop Name</th>
                  <th class="px-4 py-4 font-semibold">Owner</th>
                  <th class="px-4 py-4 font-semibold">Contact</th>
                  <th class="px-4 py-4 font-semibold">Location</th>
                  <th class="px-4 py-4 font-semibold">Status</th>
                  <th class="px-4 py-4 font-semibold">Plan</th>
                  <th class="px-4 py-4 font-semibold">Expiry Date</th>
                  <th class="px-4 py-4 font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="shop in filteredShops" :key="shop.shop_id" class="border-b border-slate-200 align-top">
                  <td class="px-4 py-4 font-semibold text-2xl">{{ shop.name }}</td>
                  <td class="px-4 py-4 text-xl">{{ shop.owner }}</td>
                  <td class="px-4 py-4 text-lg">
                    <div>{{ shop.contact_email }}</div>
                    <div class="text-slate-500">{{ shop.contact_phone }}</div>
                  </td>
                  <td class="px-4 py-4 text-xl">{{ shop.location }}</td>
                  <td class="px-4 py-4">
                    <span :class="shop.status === 'activated' ? 'badge-active' : 'badge-inactive'">
                      {{ shop.status === 'activated' ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-4">
                    <select class="form-input" :value="shop.plan" @change="changePlan(shop, $event.target.value)">
                      <option value="monthly">Monthly</option>
                      <option value="yearly">Yearly</option>
                    </select>
                  </td>
                  <td class="px-4 py-4">
                    <input class="form-input" type="date" :value="shop.expiry_date" @change="changeExpiry(shop, $event.target.value)" />
                  </td>
                  <td class="px-4 py-4">
                    <button
                      type="button"
                      :class="shop.status === 'activated' ? 'btn-danger' : 'btn-success'"
                      @click="toggleStatus(shop)"
                    >
                      {{ shop.status === 'activated' ? 'Deactivate' : 'Activate' }}
                    </button>
                  </td>
                </tr>

                <tr v-if="!filteredShops.length">
                  <td colspan="8" class="px-4 py-10 text-center text-slate-500">No shop found for this search.</td>
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
  shops: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({ total: 0, active: 0, inactive: 0 }),
  },
})

const search = ref('')

const filteredShops = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.shops

  return props.shops.filter((shop) => {
    return [shop.name, shop.owner, shop.location].some((v) => String(v || '').toLowerCase().includes(q))
  })
})

function toggleStatus(shop) {
  Inertia.post(`/admin/shops/${shop.shop_id}/toggle-status`, {}, { preserveScroll: true })
}

function changePlan(shop, plan) {
  Inertia.post(`/admin/shops/${shop.shop_id}/plan`, { plan }, { preserveScroll: true })
}

function changeExpiry(shop, expiryDate) {
  Inertia.post(`/admin/shops/${shop.shop_id}/expiry`, { expiry_date: expiryDate }, { preserveScroll: true })
}
</script>

<style scoped>
.form-input {
  width: 100%;
  min-height: 2.75rem;
  padding: 0.55rem 0.8rem;
  border: 1px solid rgba(15, 23, 42, 0.16);
  border-radius: 0.7rem;
  background: #fff;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.08);
}

.summary-box {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 0.9rem;
  background: #fff;
  padding: 1.2rem;
}

.summary-label {
  color: #475569;
}

.summary-value {
  font-size: 2.1rem;
  font-weight: 800;
  margin-top: 0.25rem;
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

.badge-active,
.badge-inactive {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 0.28rem 0.85rem;
  font-size: 1.05rem;
  font-weight: 700;
}

.badge-active {
  background: #dcfce7;
  color: #15803d;
}

.badge-inactive {
  background: #fee2e2;
  color: #dc2626;
}

.btn-danger,
.btn-success {
  border-radius: 0.7rem;
  padding: 0.5rem 0.95rem;
  font-weight: 700;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-success {
  background: #dcfce7;
  color: #15803d;
}
</style>
