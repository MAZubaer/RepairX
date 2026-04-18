<template>
  <nav class="w-full border-b border-gray-200 bg-white/95 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <div class="flex items-center">
          <a href="/dashboard/customer" class="inline-flex items-center nav-logo">
            <img :src="logo" alt="RepairiX" class="h-14 w-14 object-contain" />
          </a>
        </div>
        <div class="flex items-center space-x-4">
          <a href="/update" class="nav-link">Updates</a>
          <a href="/notifications" class="nav-link">Notifications</a>
          <a href="/about" class="nav-link">About Us</a>
          <button @click="logout" type="button" class="nav-pill nav-pill-danger">Logout</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia'

const logo = '/images/repairix_short.png'

function logout() {
  Inertia.post('/logout')
}
</script>

<style scoped>
.nav-logo {
  transition: transform 0.2s ease;
}

.nav-logo:hover {
  transform: translateY(-1px) scale(1.04);
}

.nav-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 0.6rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-dark);
  transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}

.nav-link::after {
  content: '';
  position: absolute;
  left: 0.75rem;
  right: 0.75rem;
  bottom: 0.3rem;
  height: 2px;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.22s ease;
}

.nav-link:hover {
  transform: translateY(-1px);
  background: rgba(20, 184, 166, 0.08);
  color: #0f766e;
}

.nav-link:hover::after {
  transform: scaleX(1);
}

.nav-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.45rem 0.85rem;
  border-radius: 0.65rem;
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.nav-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.26);
}

.nav-pill-danger {
  background: linear-gradient(180deg, #ef4444, #dc2626);
}

.nav-pill-danger:hover {
  filter: saturate(1.06);
}
</style>
