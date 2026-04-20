<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <CustomerNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          <div class="lg:col-span-2 space-y-3">
            <h1 class="text-5xl font-extrabold leading-tight">{{ shop?.name || 'Shop Name' }}</h1>
            <p class="text-2xl italic text-gray-500">{{ shop?.motto || 'Shop Motto' }}</p>
            <p class="text-xl">{{ shop?.address || 'Shop Address' }}</p>

            <div class="flex items-center gap-2 text-2xl">
              <span class="font-semibold">Shop Rating:</span>
              <span class="text-yellow-500 tracking-wide">{{ stars }}</span>
              <span class="text-gray-500 text-xl">({{ normalizedRating.toFixed(1) }})</span>
            </div>
          </div>

          <div>
            <div class="w-full max-w-[320px] aspect-square mx-auto lg:ml-auto rounded-lg border border-dashed border-gray-300 bg-white flex items-center justify-center overflow-hidden">
              <img v-if="logo?.url" :src="logo.url" alt="Shop Logo" class="h-full w-full object-cover" />
              <span v-else class="text-gray-400 text-lg">Shop Logo</span>
            </div>
          </div>
        </section>

        <section class="mt-8">
          <h2 class="text-4xl font-bold mb-3">Services</h2>
          <div class="rounded-lg border border-gray-300 bg-white min-h-[170px] p-5 text-lg whitespace-pre-line">
            {{ shop?.services_provided || 'Service details will appear here...' }}
          </div>

          <div class="mt-4">
            <button type="button" class="btn-primary" @click="openModal">Add Request</button>
            <p v-if="requestSentText" class="mt-2 text-green-700 text-sm font-medium">{{ requestSentText }}</p>
            <p v-if="errors.server" class="mt-2 text-red-600 text-sm">{{ errors.server[0] }}</p>
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
              <button
                v-if="img.url"
                type="button"
                class="h-full w-full"
                :aria-label="`Open Image ${idx + 1}`"
                @click="openImageViewer(img.url)"
              >
                <img :src="img.url" :alt="`Image ${idx + 1}`" class="h-full w-full object-cover" />
              </button>
              <span v-else class="text-gray-400">Image {{ idx + 1 }}</span>
            </div>
          </div>
        </section>
      </div>
    </main>

    <div v-if="showModal" class="fixed inset-0 z-40 bg-black/45 flex items-center justify-center p-4" @click.self="closeModal">
      <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-6 relative z-50">
        <button type="button" class="absolute right-4 top-3 text-gray-500 hover:text-gray-800 text-2xl" @click="closeModal">&times;</button>

        <h3 class="text-4xl font-bold mb-5">Request Application</h3>

        <form @submit.prevent="submitRequest" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold mb-1">Phone Model</label>
            <input v-model="form.phone_model" type="text" class="form-input" placeholder="e.g., iPhone 13 Pro" required />
            <div v-if="errors.phone_model" class="text-red-500 text-sm mt-1">{{ errors.phone_model[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Phone Number</label>
            <input v-model="form.phone_number" type="text" class="form-input" placeholder="Enter your phone number" required />
            <div v-if="errors.phone_number" class="text-red-500 text-sm mt-1">{{ errors.phone_number[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Phone IMEI Number</label>
            <input v-model="form.phone_imei_number" type="text" class="form-input" placeholder="Enter IMEI number" required />
            <div v-if="errors.phone_imei_number" class="text-red-500 text-sm mt-1">{{ errors.phone_imei_number[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Problem Description</label>
            <textarea v-model="form.customer_problem" class="form-input" rows="4" placeholder="Describe the issue with your device" required></textarea>
            <div v-if="errors.customer_problem" class="text-red-500 text-sm mt-1">{{ errors.customer_problem[0] }}</div>
          </div>

          <button type="submit" class="btn-primary w-full" :disabled="form.processing">Send</button>
        </form>
      </div>
    </div>

    <div v-if="showImageModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="closeImageViewer">
      <button
        type="button"
        class="absolute top-4 right-4 text-white text-4xl leading-none"
        aria-label="Close full image view"
        @click="closeImageViewer"
      >&times;</button>

      <img
        v-if="selectedImageUrl"
        :src="selectedImageUrl"
        alt="Shop full image"
        class="max-w-full max-h-[90vh] rounded-lg shadow-2xl"
      />
    </div>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import CustomerNavbar from '../../Components/CustomerNavbar.vue'

const props = defineProps({
  shop: { type: Object, required: true },
  logo: { type: Object, default: null },
  gallery: { type: Array, default: () => [] },
  requestSent: { type: Boolean, default: false },
})

const page = usePage()
const errors = computed(() => page.props.value.errors || {})

const showModal = ref(false)
const showImageModal = ref(false)
const selectedImageUrl = ref('')
const requestSentText = ref(props.requestSent ? 'Your request is sent.' : '')

const form = useForm({
  phone_model: '',
  phone_number: '',
  phone_imei_number: '',
  customer_problem: '',
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

function openModal() {
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function openImageViewer(url) {
  selectedImageUrl.value = url
  showImageModal.value = true
}

function closeImageViewer() {
  showImageModal.value = false
  selectedImageUrl.value = ''
}

function submitRequest() {
  requestSentText.value = ''

  form.post(`/customer/shops/${props.shop.shop_id}/requests`, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showModal.value = false
      requestSentText.value = 'Your request is sent.'
    },
  })
}
</script>

<style scoped>
.form-input{ width:100%; padding:.75rem 1rem; border:1px solid rgba(15,23,42,0.12); border-radius:.5rem; background:white; }
.form-input:focus{ outline:none; border-color: var(--color-primary); box-shadow: 0 0 0 4px rgba(20,184,166,0.08); }
.btn-primary{ display:inline-flex; align-items:center; justify-content:center; padding:.7rem 1.2rem; border-radius:.6rem; background:var(--color-primary); color:white; font-weight:700; }
.btn-primary:hover{ opacity:.92; }
.btn-primary:disabled{ opacity:.6; cursor:not-allowed; }
</style>
