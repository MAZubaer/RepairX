<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <AdminNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-7xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Dashboard Overview</h1>
        <p class="mt-2 text-2xl text-gray-600">Monitor your platform's key metrics and performance</p>

        <section class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <article class="summary-box">
            <div class="summary-head">
              <div>
                <p class="summary-label">Total Dealers</p>
                <p class="summary-value">{{ stats.totalDealers }}</p>
              </div>
              <span class="summary-icon">🏬</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="summary-head">
              <div>
                <p class="summary-label">Active Subscriptions</p>
                <p class="summary-value">{{ stats.activeSubscriptions }}</p>
              </div>
              <span class="summary-icon">✔</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="summary-head">
              <div>
                <p class="summary-label">Total Customers</p>
                <p class="summary-value">{{ stats.totalCustomers }}</p>
              </div>
              <span class="summary-icon">👥</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="summary-head">
              <div>
                <p class="summary-label">Total Repairs Completed</p>
                <p class="summary-value">{{ stats.totalRepairsCompleted }}</p>
              </div>
              <span class="summary-icon">🔧</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="summary-head">
              <div>
                <p class="summary-label">Total Revenue</p>
                <p class="summary-value">৳{{ formatAmount(stats.totalRevenue) }}</p>
              </div>
              <span class="summary-icon warning">৳</span>
            </div>
          </article>
        </section>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import AdminNavbar from '../../Components/AdminNavbar.vue'

defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalDealers: 0,
      activeSubscriptions: 0,
      totalCustomers: 0,
      totalRepairsCompleted: 0,
      totalRevenue: 0,
    }),
  },
})

function formatAmount(amount) {
  return Number(amount || 0).toLocaleString('en-US')
}
</script>

<style scoped>
.summary-box {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 0.9rem;
  background: #fff;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
}

.summary-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-label {
  color: #475569;
  font-size: 1.05rem;
}

.summary-value {
  font-size: 2.2rem;
  font-weight: 800;
  margin-top: 0.3rem;
}

.summary-icon {
  width: 3.2rem;
  height: 3.2rem;
  border-radius: 0.8rem;
  background: #14b8a6;
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 700;
}

.summary-icon.warning {
  background: #f59e0b;
}
</style>
