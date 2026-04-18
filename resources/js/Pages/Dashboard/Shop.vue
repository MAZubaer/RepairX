<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <ShopNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          <div class="lg:col-span-2 space-y-3">
            <h1 class="text-5xl font-extrabold leading-tight">Welcome, {{ shop?.name || 'Shop Name' }}</h1>

            <p class="text-2xl italic text-gray-500">{{ shop?.motto || 'Shop Motto' }}</p>
            <p class="text-xl">{{ shop?.address || 'Shop Address' }}</p>

            <div class="flex items-center gap-2 text-2xl">
              <span class="font-semibold">Shop Rating:</span>
              <span class="text-yellow-500 tracking-wide">{{ stars }}</span>
              <span class="text-gray-500 text-xl">({{ normalizedRating.toFixed(1) }})</span>
            </div>
          </div>

          <div>
            <div class="rounded-lg border border-dashed border-gray-300 bg-white h-[280px] flex items-center justify-center overflow-hidden">
              <img v-if="logo?.url" :src="logo.url" alt="Shop Logo" class="h-full w-full object-cover" />
              <span v-else class="text-gray-400 text-lg">Shop Logo</span>
            </div>
          </div>
        </section>

        <section class="mt-8">
          <h2 class="text-4xl font-bold mb-3">Services</h2>
          <div class="rounded-lg border border-gray-300 bg-white min-h-[170px] p-5 text-lg whitespace-pre-line">
            {{ shop?.services_provided || 'Add your services here...' }}
          </div>
        </section>

        <section class="mt-10">
          <h2 class="text-4xl font-bold mb-4">Shop Images</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="(img, idx) in visibleGallery"
              :key="img.image_id || idx"
              class="rounded-lg border border-dashed border-gray-300 bg-white h-[170px] overflow-hidden flex items-center justify-center"
            >
              <img v-if="img.url" :src="img.url" :alt="`Image ${idx + 1}`" class="h-full w-full object-cover" />
              <span v-else class="text-gray-400">Image {{ idx + 1 }}</span>
            </div>
          </div>
        </section>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ShopNavbar from '../../Components/ShopNavbar.vue'

const props = defineProps({
  shop: { type: Object, required: true },
  logo: { type: Object, default: null },
  gallery: { type: Array, default: () => [] },
})

const normalizedRating = computed(() => {
  const val = Number(props.shop?.rating ?? 0)
  if (Number.isNaN(val)) return 0
  return Math.max(0, Math.min(5, val))
})

const stars = computed(() => {
  const full = Math.round(normalizedRating.value)
  return '★'.repeat(full) + '☆'.repeat(5 - full)
})

const visibleGallery = computed(() => {
  const src = Array.isArray(props.gallery) ? props.gallery : []
  return src.filter((i) => i && i.url)
})
</script>

<style scoped>
</style>
