<script setup>
definePageMeta({
  middleware: ['admin'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="bg-white rounded-xl p-6 shadow">
      <h1 class="text-2xl font-bold text-gray-900">Admin</h1>
      <p class="text-sm text-gray-600 mt-1">
        Quản lý user và kết quả xổ số hằng ngày.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <NuxtLink
        to="/admin/users"
        class="bg-white rounded-xl p-6 shadow hover:shadow-md transition border border-gray-100"
      >
        <div class="text-lg font-bold text-gray-900">Quản lý User</div>
        <div class="text-sm text-gray-600 mt-1">
          Trạng thái VIP, permission, thông tin cơ bản.
        </div>
      </NuxtLink>

      <NuxtLink
        to="/admin/results"
        class="bg-white rounded-xl p-6 shadow hover:shadow-md transition border border-gray-100"
      >
        <div class="text-lg font-bold text-gray-900">Quản lý Result</div>
        <div class="text-sm text-gray-600 mt-1">
          Xem và sửa tay kết quả theo ngày khi crawl sai.
        </div>
      </NuxtLink>

      <NuxtLink
        to="/admin/payments"
        class="bg-white rounded-xl p-6 shadow hover:shadow-md transition border border-gray-100"
      >
        <div class="text-lg font-bold text-gray-900">Quản lý Payment</div>
        <div class="text-sm text-gray-600 mt-1">
          Duyệt thanh toán, theo dõi trạng thái và lịch sử request hoàn tất.
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
