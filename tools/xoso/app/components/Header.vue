<script setup>
import { ref } from "vue";

const isOpen = ref(false);

// fake user (sau này thay bằng API)
const user = ref(null);

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

const logout = async () => {
  // await $fetch('/api/logout')
  user.value = null;
};
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
      </nav>

      <!-- Right -->
      <div class="flex items-center gap-3">
        <!-- Nếu chưa login -->
        <template v-if="!user">
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
              @click="toggleMenu"
              class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg"
            >
              <img src="https://i.pravatar.cc/30" class="w-6 h-6 rounded-full" />
              <span>{{ user.name }}</span>
            </button>

            <!-- dropdown -->
            <div
              v-if="isOpen"
              class="absolute right-0 mt-2 w-40 bg-white shadow rounded-lg p-2"
            >
              <NuxtLink to="/profile" class="block px-3 py-2 hover:bg-gray-100">
                Profile
              </NuxtLink>
              <button
                @click="logout"
                class="w-full text-left px-3 py-2 hover:bg-gray-100"
              >
                Logout
              </button>
            </div>
          </div>
        </template>

        <!-- Mobile button -->
        <button @click="toggleMenu" class="md:hidden">☰</button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="isOpen" class="md:hidden px-4 pb-4">
      <NuxtLink to="/" class="block py-2">Trang chủ</NuxtLink>
      <NuxtLink to="/dashboard" class="block py-2">Dashboard</NuxtLink>
      <NuxtLink to="/results" class="block py-2">Kết quả</NuxtLink>
      <NuxtLink to="/vip" class="block py-2 text-yellow-500">VIP</NuxtLink>
    </div>
  </header>
</template>
