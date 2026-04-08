<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <main>
      <slot />
    </main>
    <footer class="bg-gray-800 text-white py-12 mt-20">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
          <div>
            <h3 class="text-2xl font-bold mb-4">RealEstate</h3>
            <p class="text-gray-400">Your trusted partner in finding the perfect property for sale or rent.</p>
          </div>
          <div>
            <h4 class="text-xl font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-2">
              <li><NuxtLink to="/" class="hover:text-blue-400 transition-colors">Home</NuxtLink></li>
              <li><NuxtLink to="/properties" class="hover:text-blue-400 transition-colors">Properties</NuxtLink></li>
              <li><NuxtLink to="/agents" class="hover:text-blue-400 transition-colors">Agents</NuxtLink></li>
            </ul>
          </div>
          <div>
            <h4 class="text-xl font-semibold mb-4">Account</h4>
            <ul class="space-y-2">
              <li><NuxtLink to="/login" class="hover:text-blue-400 transition-colors">Login</NuxtLink></li>
              <li><NuxtLink to="/register" class="hover:text-blue-400 transition-colors">Register</NuxtLink></li>
              <li><NuxtLink to="/dashboard" class="hover:text-blue-400 transition-colors">Dashboard</NuxtLink></li>
            </ul>
          </div>
          <div>
            <h4 class="text-xl font-semibold mb-4">Contact</h4>
            <p class="text-gray-400">123 Street Name, City, Country</p>
            <p class="text-gray-400">support@realestate.com</p>
            <p class="text-gray-400">+123 456 789</p>
          </div>
        </div>
        <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-500">
          &copy; 2026 RealEstate. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from '~/components/Navbar.vue';
</script>
