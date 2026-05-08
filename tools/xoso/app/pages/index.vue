<script setup>
const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Trang chủ',
  description: 'XoSo AI: phân tích xổ số bằng dữ liệu, theo dõi thống kê, lô gan, tần suất và gợi ý số mỗi ngày.',
  ogTitle: 'XoSo AI - Phân tích xổ số bằng dữ liệu',
  ogDescription: 'Theo dõi thống kê, lô gan, tần suất và gợi ý số mỗi ngày.',
  ogUrl: canonical,
  twitterTitle: 'XoSo AI - Phân tích xổ số bằng dữ liệu',
  twitterDescription: 'Theo dõi thống kê, lô gan, tần suất và gợi ý số mỗi ngày.',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
});

const stats = ref([]);
const predictions = ref([]);
const loading = ref(true);
const dice = ref(["--", "--", "--"]);
let diceTimer = null;
const api = useApi();
import { useAuthStore } from "~/stores/auth";
const authStore = useAuthStore();
onMounted(async () => {
  try {
    startDice();
    const data = await api.getStats();
    stats.value = data.data;
    const predictionsData = await api.getPredictions();
    predictions.value = predictionsData.data?.predictions || [];
  } catch (e) {
    console.log(e);
  } finally {
    loading.value = false;
    stopDice();
  }
});

const startDice = () => {
  if (diceTimer) return;
  diceTimer = setInterval(() => {
    dice.value = Array.from({ length: 3 }, () =>
      String(Math.floor(Math.random() * 100)).padStart(2, "0")
    );
  }, 120);
};

const stopDice = () => {
  if (diceTimer) {
    clearInterval(diceTimer);
    diceTimer = null;
  }
};

onBeforeUnmount(() => {
  stopDice();
});
</script>

<template>
  <div>
    <!-- HERO -->
    <section
      class="text-center py-16 px-4 bg-gradient-to-r from-red-500 to-orange-500 text-white"
    >
      <h1 class="text-4xl font-bold mb-4">Phân tích xổ số bằng dữ liệu</h1>

      <p class="opacity-90 mb-6">Xem thống kê, lô gan, tần suất và gợi ý số mỗi ngày</p>

      <div class="flex justify-center gap-4 max-sm:flex-col">
        <NuxtLink
          to="/dashboard"
          class="bg-white text-red-500 px-6 py-3 rounded-lg font-semibold"
        >
          Xem thống kê
        </NuxtLink>

        <NuxtLink
          to="/vip"
          class="bg-yellow-400 text-black px-6 py-3 rounded-lg font-semibold"
          v-if="!authStore.isVip"
        >
          🚀 Nâng cấp VIP
        </NuxtLink>
      </div>
    </section>

    <!-- PREVIEW GRID (gây tò mò) -->
    <section class="max-w-6xl mx-auto py-8 md:py-12 px-4">
      <h2 class="text-2xl font-bold mb-6 text-center">🔥 Số nổi bật hôm nay</h2>

      <div v-if="loading" class="flex justify-center flex-wrap gap-2">
        <div
          v-for="(n, idx) in dice"
          :key="`dice-${idx}`"
          class="bg-white text-red-600 px-4 py-3 rounded-lg font-extrabold text-center shadow-sm border border-red-100"
        >
          <div class="text-lg tabular-nums tracking-wider">{{ n }}</div>
        </div>
      </div>

      <div v-else class="flex justify-center flex-wrap gap-2">
        <div
          v-for="(item, idx) in predictions.ranking?.numbers || []"
          :key="idx"
          class="bg-red-500 text-white px-4 py-3 rounded-lg font-bold text-center"
        >
          <div class="text-lg">{{ item.number }}</div>
        </div>
      </div>
    </section>

    <section class="max-w-6xl mx-auto py-6 md:py-10 px-4">
      <MiniGamePrediction />
    </section>

    <!-- FEATURES -->
    <section class="bg-gray-50 py-8 md:py-16 px-4">
      <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8 text-center">
        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">📊 Thống kê chi tiết</h3>
          <p class="text-sm opacity-70">
            Phân tích tần suất, chu kỳ và lịch sử xuất hiện
          </p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">🔥 Lô gan</h3>
          <p class="text-sm opacity-70">Theo dõi các số lâu chưa xuất hiện</p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">🎯 Gợi ý mỗi ngày</h3>
          <p class="text-sm opacity-70">Thuật toán phân tích đưa ra số tham khảo</p>
        </div>
      </div>
    </section>

    <CTA />
  </div>
</template>
