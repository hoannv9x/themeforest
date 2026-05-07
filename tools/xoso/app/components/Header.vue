<script setup>
import { ref, watch } from "vue";
import { useAuthStore } from "~/stores/auth";
import { useRoute } from "#app";

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

watch(
  () => route.fullPath,
  () => {
    closeMobileMenu();
    closeUserMenu();
  }
);
</script>

<template>
  <header class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      <!-- Logo -->
      <NuxtLink to="/" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-logo-medium md:bg-logo bg-contain bg-center bg-no-repeat"></div>
        <span class="font-bold text-lg">XoSo AI</span>
      </NuxtLink>

      <!-- Menu desktop -->
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <NuxtLink to="/">Trang chủ</NuxtLink>
        <NuxtLink to="/dashboard">Dashboard</NuxtLink>
        <NuxtLink to="/results">Kết quả</NuxtLink>
        <NuxtLink to="/vip" class="text-yellow-500 font-semibold"> VIP </NuxtLink>
        <NuxtLink
          v-if="authStore.user?.permission === 'developer' || authStore.user?.role === 'admin'"
          to="/admin"
          class="text-blue-600 font-semibold"
        >
          Admin
        </NuxtLink>
        <!-- <NuxtLink to="/api-register" class="text-blue-600 font-semibold"> API </NuxtLink>
        <NuxtLink to="/api-playground">API Test</NuxtLink> -->
      </nav>

      <!-- Right -->
      <div class="flex items-center gap-3">
        <!-- VIP Status Badge -->
        <template v-if="authStore.isAuthenticated && authStore.isVip">
          <NuxtLink
            to="/vip"
            class="flex items-center gap-1 bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold"
          >
            <span v-if="authStore.isTrial">🔥 VIP Trial</span>
            <span v-else>⭐ VIP</span>
            <span
              v-if="authStore.vipStatus?.vip_remaining_days !== null"
              class="bg-yellow-200 px-1.5 py-0.5 rounded"
            >
              {{ authStore.vipStatus.vip_remaining_days }}d
            </span>
          </NuxtLink>
        </template>
        <!-- Nếu chưa login -->
        <template v-if="!authStore.isAuthenticated">
          <NuxtLink to="/login" class="text-sm max-sm:hidden">Đăng nhập</NuxtLink>
          <NuxtLink
            to="/register"
            class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm max-sm:hidden"
          >
            Đăng ký (3 ngày VIP free)
          </NuxtLink>
        </template>

        <!-- Nếu đã login -->
        <template v-else>
          <div class="relative">
            <button
              @click="toggleUserMenu"
              class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg"
            >
              <img
                :src="authStore.user.avatar_url || 'https://i.pravatar.cc/30'"
                class="w-6 h-6 rounded-full max-sm:w-4 max-sm:h-4 object-cover"
              />
              <span class="max-sm:hidden">{{ authStore.user.name }}</span>
            </button>

            <!-- dropdown -->
            <div
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-2 w-48 bg-white shadow rounded-lg p-2"
            >
              <div v-if="authStore.isVip" class="px-3 py-2 border-b border-gray-100 mb-2">
                <p
                  class="text-xs font-semibold"
                  :class="authStore.isTrial ? 'text-yellow-600' : 'text-green-600'"
                >
                  {{ authStore.isTrial ? "VIP Trial" : "VIP Active" }}
                </p>
                <p class="text-xs text-gray-500">
                  Còn lại: {{ authStore.vipStatus?.vip_remaining_days }} ngày
                </p>
              </div>
              <NuxtLink
                to="/vip"
                class="block px-3 py-2 hover:bg-gray-100 rounded"
                @click="closeUserMenu"
              >
                Nâng cấp VIP
              </NuxtLink>
              <NuxtLink
                v-if="authStore.user?.permission === 'developer' || authStore.user?.role === 'admin'"
                to="/admin"
                class="block px-3 py-2 hover:bg-gray-100 rounded"
                @click="closeUserMenu"
              >
                Admin
              </NuxtLink>
              <button
                @click="handleLogout"
                class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded text-red-600"
              >
                Đăng xuất
              </button>
            </div>
          </div>
        </template>

        <!-- Mobile button -->
        <button @click="toggleMobileMenu" class="md:hidden">☰</button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="isMobileMenuOpen" class="md:hidden px-2 pb-4">
      <NuxtLink to="/" class="block px-4 py-2" @click="closeMobileMenu">Trang chủ</NuxtLink>
      <NuxtLink to="/dashboard" class="block px-4 py-2" @click="closeMobileMenu"
        >Thống kê</NuxtLink
      >
      <NuxtLink to="/results" class="block px-4 py-2" @click="closeMobileMenu"
        >Kết quả</NuxtLink
      >
      <NuxtLink to="/vip" class="block px-4 py-2 text-yellow-500" @click="closeMobileMenu"
        >VIP</NuxtLink
      >
      <NuxtLink
        v-if="authStore.user?.permission === 'developer' || authStore.user?.role === 'admin'"
        to="/admin"
        class="block px-4 py-2 text-blue-600 font-semibold"
        @click="closeMobileMenu"
      >
        Admin
      </NuxtLink>
      <NuxtLink v-if="!authStore.isAuthenticated" to="/login" class="text-sm sm:hidden block px-4 py-2">Đăng nhập</NuxtLink>
      <NuxtLink v-if="!authStore.isAuthenticated" to="/register" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm sm:hidden block">
        Đăng ký (3 ngày VIP miễn phí)
      </NuxtLink>
      <!-- <NuxtLink to="/api-register" class="block py-2 text-blue-600" @click="closeMobileMenu">API</NuxtLink> -->
      <!-- <NuxtLink to="/api-playground" class="block py-2" @click="closeMobileMenu">API Test</NuxtLink> -->
    </div>
  </header>
</template>
