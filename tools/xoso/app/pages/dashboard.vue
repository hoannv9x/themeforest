<script setup>
const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: "Dashboard",
});
useHead({
  link: [{ rel: "canonical", href: canonical }],
  meta: [{ name: "robots", content: "noindex, nofollow" }],
});

import { useAuthStore } from "~/stores/auth";
const authStore = useAuthStore();
const { formatDate } = useFormatters();
const stats = ref([]);
const predictions = ref([]);
const yesterdayPredictions = ref(null);
const numbersMost = ref([]);
const api = useApi();
const paramNumberMost = ref({
  region: "mb",
});

onMounted(async () => {
  authStore.initAuth();
  if (authStore.isAuthenticated && !authStore.vipStatus) {
    try {
      await authStore.fetchMe();
    } catch (e) {}
  }
  const statsFn = authStore.isVip ? api.getVipStats : api.getStats;
  const data = await statsFn({ region: "mb" });
  stats.value = data.data || [];

  const mostFn = authStore.isVip
    ? api.getVipMostFrequentNumbers
    : api.getMostFrequentNumbers;
  const numberData = await mostFn(paramNumberMost.value);
  numbersMost.value = numberData.data;

  const predictionsFn = authStore.isVip ? api.getVipPredictions : api.getPredictions;
  const predictionsData = await predictionsFn();
  predictions.value = predictionsData.data;

  const yesterdayFn = authStore.isVip
    ? api.getVipYesterdayPredictions
    : api.getYesterdayPredictions;
  const yesterdayPredictionsData = await yesterdayFn();
  yesterdayPredictions.value = yesterdayPredictionsData.data;
});

const isVip = computed(() => authStore.isVip);
const isTrial = computed(() => authStore.isTrial);
const vipStatus = computed(() => authStore.vipStatus);
const isNearExpired = computed(() => {
  if (!vipStatus.value?.vip_expired_at) return false;
  return (
    new Date(vipStatus.value.vip_expired_at).getTime() - Date.now() <
    1000 * 60 * 60 * 24 * 3
  );
});

const handleSelectedDate = async (day) => {
  paramNumberMost.value.day = day;
  const mostFn = authStore.isVip
    ? api.getVipMostFrequentNumbers
    : api.getMostFrequentNumbers;
  const numberData = await mostFn(paramNumberMost.value);
  numbersMost.value = numberData.data;
};
</script>

<template>
  <div class="max-w-6xl mx-auto p-4 space-y-6">
    <MiniGamePrediction />
    <div
      v-if="isVip"
      class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 max-sm:p-3"
    >
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="text-3xl">{{ isTrial ? "🔥" : "⭐" }}</div>
          <div>
            <h2 class="text-xl font-bold text-green-700">
              {{ isTrial ? "VIP Trial Active" : "VIP Active" }}
            </h2>
            <p class="text-green-600">
              Hết hạn:
              {{ formatDate(vipStatus?.vip_expired_at, true, "DD-MM-YYYY HH:mm:ss") }} ({{
                vipStatus?.vip_remaining_days
              }}
              ngày còn lại)
            </p>
          </div>
        </div>
        <NuxtLink
          v-if="isNearExpired"
          to="/vip"
          class="bg-yellow-400 text-black px-4 py-2 rounded-lg text-sm font-semibold"
        >
          Gia hạn VIP
        </NuxtLink>
      </div>

      <PredictionCard class="mt-6" :data="predictions" />
      <YesterdayPredictionHits
        class="mt-6"
        v-if="yesterdayPredictions"
        :data="yesterdayPredictions"
      />

      <div v-if="predictions?.is_vip && predictions?.heatmap" class="mt-6 space-y-4">
        <h3 class="text-lg font-bold">🔥 Heatmap số (VIP)</h3>
        <div class="grid grid-cols-10 gap-2 max-sm:grid-cols-5">
          <div
            v-for="(item, index) in Object.values(predictions.heatmap)"
            :key="index"
            class="relative group"
          >
            <BasePopover classCustom="w-64 max-sm:w-48">
              <template #trigger>
                <button
                  :class="[
                    'w-full aspect-square rounded-lg flex items-center justify-center font-bold text-sm transition-all border-2',
                    item.is_in_prediction
                      ? 'border-purple-500 ring-2 ring-purple-200'
                      : 'border-transparent',
                    item.was_hit_same_day_last_year && !item.is_in_prediction
                      ? 'border-blue-500 ring-2 ring-blue-200'
                      : '',
                    item.confidence > 70
                      ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-lg scale-105'
                      : item.confidence > 50
                      ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white'
                      : item.confidence > 30
                      ? 'bg-gradient-to-br from-yellow-400 to-yellow-500 text-black'
                      : 'bg-gray-200 text-gray-600',
                  ]"
                >
                  {{ item.number }}
                </button>
              </template>

              <div class="z-50 bg-white rounded-lg shadow-2xl p-3 border border-gray-200">
                <div class="text-center mb-2">
                  <span
                    :class="[
                      'inline-block px-2 py-1 rounded text-xs font-bold',
                      item.confidence > 70
                        ? 'bg-red-100 text-red-700'
                        : item.confidence > 50
                        ? 'bg-orange-100 text-orange-700'
                        : item.confidence > 30
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-gray-100 text-gray-700',
                    ]"
                  >
                    Confidence: {{ item.confidence }}%
                  </span>
                </div>
                <div class="text-xs text-gray-700 space-y-1">
                  <div>Tổng số lần về: {{ item.total_count }}</div>
                  <div>Gan: {{ item.current_gap }}/{{ item.max_gap }}</div>
                  <div>
                    Về gần nhất:
                    {{ item.last_hit_date ? formatDate(item.last_hit_date) : "--" }}
                  </div>
                  <div
                    v-if="item.was_hit_same_day_last_year"
                    class="text-blue-700 font-semibold"
                  >
                    Đã về đúng ngày này của năm trước
                  </div>
                </div>
              </div>
            </BasePopover>
          </div>
        </div>
      </div>
    </div>

    <template v-else>
      <PredictionCard :data="predictions" />
      <YesterdayPredictionHits v-if="yesterdayPredictions" :data="yesterdayPredictions" />
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <StatsTable :data="stats" />
      <ChartFrequency :data="numbersMost" @selectedDate="handleSelectedDate" />
    </div>

    <NumberGrid :data="stats" v-if="!isVip" />
    <CTA />
  </div>
</template>
