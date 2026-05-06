<template>
  <!-- CTA -->
  <section class="text-center py-8 md:16 px-4" v-if="!authStore.isVip">
    <h2 class="text-3xl font-bold mb-4">Muốn xem gợi ý chính xác hơn?</h2>

    <p class="mb-6 opacity-70">
      Nâng cấp VIP để xem phân tích nâng cao và dự đoán mỗi ngày
    </p>

    <NuxtLink
      to="/vip"
      class="bg-red-500 text-white px-8 py-3 rounded-lg text-md md:text-lg font-semibold"
    >
      🚀 Nâng cấp VIP ngay
    </NuxtLink>
  </section>
</template>


<script setup>
import { useAuthStore } from "~/stores/auth";
const authStore = useAuthStore();
</script>