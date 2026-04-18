<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <!-- Navbar component -->
    <Navbar />

    <!-- Main -->
    <main class="flex-1 flex items-center justify-center">
      <div class="w-full max-w-3xl text-center px-6 py-12">
        <img :src="logo" alt="RepairiX logo" class="mx-auto h-28 w-28 rounded-md shadow-sm bg-white" />
        <h1 class="mt-8 text-5xl font-extrabold text-dark">RepairiX</h1>
        <p class="mt-3 text-lg text-gray-600">Your All-in-One Repair Solution</p>

        <div class="mt-10 flex items-center justify-center gap-6">
          <a href="/register/customer" class="btn-outline-primary">Register as Customer</a>
          <a href="/register/shop" class="btn-outline-primary">Register as Shop</a>
        </div>
      </div>
    </main>

    <!-- small footer spacing -->
    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import Navbar from '../Components/Navbar.vue'
const logo = '/images/repairix_short.png'
</script>

<style scoped>
</style>
