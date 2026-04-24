<script setup>
import { ref, watch } from "vue";
import { useAuthStore } from '~/stores/auth';
import { useRoute } from '#app';

const authStore = useAuthStore();
const route = useRoute();
const isUserMenuOpen = ref(false);
const isMobileMenuOpen = ref(false);

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const closeUserMenu = () => {
  isUserMenuOpen.value = false;
};

const handleLogout = async () => {
  closeUserMenu();
  closeMobileMenu();
  await authStore.logout();
};

watch(() => route.fullPath, () => {
  closeMobileMenu();
  closeUserMenu();
});
</script>

<template>
  <header class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-red-500 rounded-lg"></div>
        <span class="font-bold text-lg">XoSo AI</span>
      </div>

      <!-- Menu desktop -->
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <NuxtLink to="/">Trang chủ</NuxtLink>
        <NuxtLink to="/dashboard">Dashboard</NuxtLink>
        <NuxtLink to="/results">Kết quả</NuxtLink>
        <NuxtLink to="/vip" class="text-yellow-500 font-semibold"> VIP </NuxtLink>
        <NuxtLink to="/api-register" class="text-blue-600 font-semibold"> API </NuxtLink>
        <NuxtLink to="/api-playground">API Test</NuxtLink>
      </nav>

      <!-- Right -->
      <div class="flex items-center gap-3">
        <!-- Nếu chưa login -->
        <template v-if="!authStore.isAuthenticated">
          <NuxtLink to="/login" class="text-sm">Đăng nhập</NuxtLink>
          <NuxtLink
            to="/register"
            class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm"
          >
            Đăng ký
          </NuxtLink>
        </template>

        <!-- Nếu đã login -->
        <template v-else>
          <div class="relative">
            <button
              @click="toggleUserMenu"
              class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg"
            >
              <img src="https://i.pravatar.cc/30" class="w-6 h-6 rounded-full" />
              <span>{{ authStore.user.name }}</span>
            </button>

            <!-- dropdown -->
            <div
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-2 w-40 bg-white shadow rounded-lg p-2"
            >
              <NuxtLink to="/profile" class="block px-3 py-2 hover:bg-gray-100" @click="closeUserMenu">
                Profile
              </NuxtLink>
              <button
                @click="handleLogout"
                class="w-full text-left px-3 py-2 hover:bg-gray-100"
              >
                Logout
              </button>
            </div>
          </div>
        </template>

        <!-- Mobile button -->
        <button @click="toggleMobileMenu" class="md:hidden">☰</button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="isMobileMenuOpen" class="md:hidden px-4 pb-4">
      <NuxtLink to="/" class="block py-2" @click="closeMobileMenu">Trang chủ</NuxtLink>
      <NuxtLink to="/dashboard" class="block py-2" @click="closeMobileMenu">Dashboard</NuxtLink>
      <NuxtLink to="/results" class="block py-2" @click="closeMobileMenu">Kết quả</NuxtLink>
      <NuxtLink to="/vip" class="block py-2 text-yellow-500" @click="closeMobileMenu">VIP</NuxtLink>
      <NuxtLink to="/api-register" class="block py-2 text-blue-600" @click="closeMobileMenu">API</NuxtLink>
      <NuxtLink to="/api-playground" class="block py-2" @click="closeMobileMenu">API Test</NuxtLink>
    </div>
  </header>
</template>
