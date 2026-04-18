<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <Navbar v-if="role === null" />
    <CustomerNavbar v-else-if="role === 'customer'" />
    <ShopNavbar v-else-if="role === 'shop'" />
    <Navbar v-else />

    <main class="flex-1 py-12 px-4 sm:px-6">
      <div class="mx-auto w-full max-w-6xl">
        <section class="text-center">
          <h1 class="text-4xl md:text-6xl font-extrabold text-dark">About RepairiX</h1>
          <p class="mt-4 text-lg text-slate-600">
            Connecting customers with trusted repair shops for mobile devices.
          </p>
        </section>

        <section class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <article class="about-card">
            <h2 class="text-4xl font-bold text-dark">Our Mission</h2>
            <p class="mt-4 text-slate-600 text-lg leading-8">
              We aim to simplify the process of repairing mobile phones by connecting you with
              verified, professional repair shops in your area.
            </p>
          </article>

          <article class="about-card">
            <h2 class="text-4xl font-bold text-dark">Why Choose Us</h2>
            <p class="mt-4 text-slate-600 text-lg leading-8">
              RepairiX provides a seamless platform for customers and repair shops with transparent pricing,
              quality service, and dependable turnaround for mobile repairs.
            </p>
          </article>
        </section>

        <section class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <article class="feature-item">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <rect x="7" y="2" width="10" height="20" rx="2" stroke-width="2" />
                <circle cx="12" cy="18" r="1" stroke-width="2" />
              </svg>
            </div>
            <h3 class="feature-title">Mobile Phones</h3>
            <p class="feature-text">Focused support for smartphone repair needs.</p>
          </article>

          <article class="feature-item">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 011.4 1.4l-8 8a1 1 0 01-.46.26l-3 1a1 1 0 01-1.26-1.26l1-3a1 1 0 01.26-.46l8-8z" />
              </svg>
            </div>
            <h3 class="feature-title">Expert Repairs</h3>
            <p class="feature-text">Verified and skilled technicians.</p>
          </article>

          <article class="feature-item">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="9" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2" />
              </svg>
            </div>
            <h3 class="feature-title">Fast Service</h3>
            <p class="feature-text">Quick turnaround times.</p>
          </article>

          <article class="feature-item">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="feature-title">Quality Assured</h3>
            <p class="feature-text">Guaranteed workmanship.</p>
          </article>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import Navbar from '../Components/Navbar.vue'
import CustomerNavbar from '../Components/CustomerNavbar.vue'
import ShopNavbar from '../Components/ShopNavbar.vue'

defineProps({
  role: {
    type: String,
    default: null,
  },
})
</script>

<style scoped>
.about-card {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.72);
  padding: 2rem;
}

.feature-item {
  text-align: center;
}

.feature-icon {
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 4.25rem;
  height: 4.25rem;
  border-radius: 999px;
  color: #0d9488;
  background: rgba(20, 184, 166, 0.15);
}

.feature-title {
  margin-top: 1rem;
  font-size: 1.9rem;
  font-weight: 700;
  color: #0f172a;
}

.feature-text {
  margin-top: 0.35rem;
  color: #64748b;
  font-size: 1.05rem;
}
</style>
