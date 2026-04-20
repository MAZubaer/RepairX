<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <CustomerNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Welcome, {{ customerName || 'Customer' }}!</h1>
        <p class="mt-3 text-xl text-gray-600">Find the best repair shops near you</p>

        <div class="mt-6 max-w-xl">
          <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 014.314 8.91l3.638 3.638a1 1 0 01-1.414 1.414l-3.638-3.638A5.5 5.5 0 118.5 3zm0 2a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" clip-rule="evenodd" />
              </svg>
            </span>
            <input
              v-model="search"
              type="text"
              class="form-input search-input text-lg"
              placeholder="Search shop name..."
            />
          </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          <article
            v-for="shop in filteredShops"
            :key="shop.shop_id"
            class="h-full rounded-lg border border-gray-300 bg-white overflow-hidden shadow-sm flex flex-col"
          >
            <div class="h-52 bg-gray-100 border-b border-gray-200 flex items-center justify-center overflow-hidden">
              <img v-if="shop.logo_url" :src="shop.logo_url" :alt="`${shop.name} logo`" class="h-full w-full object-cover" />
              <span v-else class="text-gray-400">Shop Image</span>
            </div>

            <div class="p-4 flex flex-col gap-2 flex-1">
              <h3 class="text-3xl font-bold leading-tight shop-name">{{ shop.name }}</h3>

              <div class="flex items-center gap-2 text-xl">
                <span class="text-yellow-500">{{ shopStars(shop.rating) }}</span>
                <span class="text-gray-500">({{ Number(shop.rating || 0).toFixed(1) }})</span>
              </div>

              <p class="text-gray-600 text-lg shop-location">📍 {{ shop.location }}</p>

              <a
                :href="`/customer/shops/${shop.shop_id}`"
                class="mt-auto w-full inline-flex justify-center rounded-md bg-primary text-white py-2.5 font-semibold hover:opacity-90"
              >
                View Details
              </a>
            </div>
          </article>
        </div>

        <p v-if="!filteredShops.length" class="mt-10 text-center text-gray-500 text-lg">
          No shops found for "{{ search }}".
        </p>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import CustomerNavbar from '../../Components/CustomerNavbar.vue'

const props = defineProps({
  customerName: { type: String, default: 'Customer' },
  shops: { type: Array, default: () => [] },
})

const search = ref('')

const filteredShops = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.shops
  return props.shops.filter((shop) => (shop.name || '').toLowerCase().includes(q))
})

function shopStars(rating) {
  const value = Number(rating || 0)
  const normalized = Math.max(0, Math.min(5, value))
  const full = Math.round(normalized)
  return '★'.repeat(full) + '☆'.repeat(5 - full)
}
</script>

<style scoped>
.form-input{ width:100%; min-height:3.25rem; padding:.75rem 1rem; border:1px solid rgba(15,23,42,0.12); border-radius:.75rem; background:white; }
.form-input:focus{ outline:none; border-color: var(--color-primary); box-shadow: 0 0 0 4px rgba(20,184,166,0.08); }
.form-input::placeholder{ color:#7a869a; }
.search-input{ padding-left:3.1rem; }
.shop-name{ min-height: 3.25rem; }
.shop-location{ min-height: 3.5rem; }
</style>
