<script setup>
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Thông tin tài khoản',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const authStore = useAuthStore();
authStore.initAuth();

if (!authStore.getIsAuthenticated) {
  await navigateTo("/login");
}

if (!authStore.getUser) {
  try {
    await authStore.fetchMe();
  } catch (e) {
    await authStore.logout();
    await navigateTo("/login");
  }
}

const vipStatus = computed(() => authStore.vipStatus);
</script>

<template>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">Thông tin tài khoản</h1>
      <p class="text-gray-600 mt-2">Thông tin user đang đăng nhập.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white border rounded-xl p-6 md:col-span-2">
        <div class="flex items-center gap-4">
          <img
            :src="authStore.user?.avatar_url || 'https://i.pravatar.cc/80'"
            class="w-16 h-16 rounded-full object-cover border"
          />
          <div>
            <div class="text-lg font-semibold">{{ authStore.user?.name }}</div>
            <div class="text-sm text-gray-600">{{ authStore.user?.email }}</div>
            <div class="text-xs text-gray-500 mt-1">
              Role: <span class="font-mono">{{ authStore.user?.role }}</span> · Permission:
              <span class="font-mono">{{ authStore.user?.permission }}</span>
            </div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div class="border rounded-lg p-3">
            <div class="text-gray-500">VIP</div>
            <div class="font-semibold">
              {{ vipStatus?.is_vip ? (vipStatus?.is_trial ? "VIP Trial" : "VIP Active") : "Chưa kích hoạt" }}
            </div>
            <div v-if="vipStatus?.vip_remaining_days !== null" class="text-gray-600">
              Còn lại: <span class="font-mono">{{ vipStatus.vip_remaining_days }}</span> ngày
            </div>
          </div>
          <div class="border rounded-lg p-3">
            <div class="text-gray-500">Tài khoản</div>
            <div class="space-x-3 mt-1">
              <NuxtLink to="/transactions" class="text-blue-600 hover:underline">Lịch sử giao dịch</NuxtLink>
              <NuxtLink to="/coupons" class="text-blue-600 hover:underline">Coupon</NuxtLink>
            </div>
            <div class="mt-2">
              <NuxtLink to="/change-password" class="text-blue-600 hover:underline">Đổi mật khẩu</NuxtLink>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white border rounded-xl p-6">
        <div class="text-sm font-semibold mb-2">Menu nhanh</div>
        <div class="space-y-2 text-sm">
          <NuxtLink to="/dashboard" class="block text-blue-600 hover:underline">Dashboard</NuxtLink>
          <NuxtLink to="/vip" class="block text-blue-600 hover:underline">VIP</NuxtLink>
          <!-- <NuxtLink to="/api-register" class="block text-blue-600 hover:underline">API</NuxtLink> -->
        </div>
      </div>
    </div>

    <Share />
  </div>
</template>

