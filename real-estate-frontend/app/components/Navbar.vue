<template>
  <nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center">
            <NuxtLink to="/" class="flex items-center">
              <span class="text-2xl font-bold text-blue-600">RealEstate</span>
            </NuxtLink>
          </div>
          <!-- Primary Navigation -->
          <div class="hidden md:ml-8 md:flex md:space-x-8 items-center">
            <NuxtLink to="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors" active-class="text-blue-600 border-b-2 border-blue-600">
              Home
            </NuxtLink>
            <NuxtLink to="/properties" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors" active-class="text-blue-600 border-b-2 border-blue-600">
              Properties
            </NuxtLink>
            <NuxtLink to="/agents" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors" active-class="text-blue-600 border-b-2 border-blue-600">
              Agents
            </NuxtLink>
          </div>
        </div>

        <!-- Secondary Navigation (Auth) -->
        <div class="hidden md:flex items-center space-x-4">
          <template v-if="authStore.isAuthenticated">
            <NuxtLink to="/dashboard" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">
              Dashboard
            </NuxtLink>
            <button @click="handleLogout" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
              Logout
            </button>
          </template>
          <template v-else>
            <NuxtLink to="/login" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">
              Login
            </NuxtLink>
            <NuxtLink to="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
              Register
            </NuxtLink>
          </template>
          
          <!-- Admin Portal Link (Small) -->
          <NuxtLink v-if="!authStore.isAdminAuthenticated" to="/admin/login" class="text-xs text-gray-400 hover:text-gray-600 ml-4 border-l pl-4">
            Admin
          </NuxtLink>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button @click="showMobileMenu = !showMobileMenu" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-show="showMobileMenu" class="md:hidden bg-white border-t">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <NuxtLink to="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Home</NuxtLink>
        <NuxtLink to="/properties" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Properties</NuxtLink>
        <NuxtLink to="/agents" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Agents</NuxtLink>
        
        <div class="border-t border-gray-200 pt-4 mt-4">
          <template v-if="authStore.isAuthenticated">
            <NuxtLink to="/dashboard" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Dashboard</NuxtLink>
            <button @click="handleLogout" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-50">Logout</button>
          </template>
          <template v-else>
            <NuxtLink to="/login" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Login</NuxtLink>
            <NuxtLink to="/register" class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 hover:bg-gray-50">Register</NuxtLink>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '~/stores/auth';
import { useRouter } from '#app';

const authStore = useAuthStore();
const router = useRouter();
const showMobileMenu = ref(false);

const handleLogout = async () => {
  await authStore.logout();
  showMobileMenu.value = false;
  router.push('/login');
};
</script>