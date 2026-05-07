<script setup>
const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'VIP',
  description: 'Nâng cấp VIP để xem đầy đủ bộ số + phân tích, heatmap 100 số, thống kê xác suất và gợi ý chiến lược.',
  ogTitle: 'VIP - XoSo AI',
  ogDescription: 'Nâng cấp VIP để xem đầy đủ bộ số + phân tích, heatmap 100 số, thống kê xác suất và gợi ý chiến lược.',
  ogUrl: canonical,
  twitterTitle: 'VIP - XoSo AI',
  twitterDescription: 'Nâng cấp VIP để xem đầy đủ bộ số + phân tích, heatmap 100 số, thống kê xác suất và gợi ý chiến lược.',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
});

import { useAuthStore } from "~/stores/auth";
const { formatDate } = useFormatters();

const api = useApi();
const authStore = useAuthStore();
const loading = ref(false);
const plans = ref({});
const selectedPlan = ref("vip_30d");
const activePayment = ref(null);
const paymentHistory = ref([]);
const qrContent = ref("");
const pollTimer = ref(null);
const countdownTimer = ref(null);
const nowTick = ref(Date.now());
const predictions = ref(null);
const yesterdayPredictions = ref(null);
const err = ref(null);
const completePaymentLoading = ref(false);
const upsellData = ref(null);

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

const qrUrl = computed(() => qrContent.value || "");

const selectedPlanInfo = computed(() => plans.value?.[selectedPlan.value] || null);

const stopPolling = () => {
  if (pollTimer.value) {
    clearInterval(pollTimer.value);
    pollTimer.value = null;
  }
};

async function getVipPredictions() {
  try {
    const { data } = await api.getVipPredictions();
    predictions.value = data;
  } catch (e) {
    err.value = e;
  }
}

async function getVipYesterdayPredictions() {
  try {
    const { data } = await api.getVipYesterdayPredictions();
    yesterdayPredictions.value = data;
  } catch (e) {
    err.value = e;
  }
}

async function fetchPlans() {
  const response = await api.getPaymentPlans();
  plans.value = response.data.vip || {};
}

async function fetchUpsell() {
  try {
    const response = await api.getVipUpsell();
    upsellData.value = response.data;
  } catch (e) {
    console.error("Failed to fetch upsell:", e);
  }
}

async function fetchPaymentHistory() {
  const response = await api.getPaymentHistory();
  paymentHistory.value = response.data || [];
}

async function startVipPayment() {
  loading.value = true;
  err.value = null;
  try {
    const response = await api.createPayment({
      type: "vip",
      plan_key: selectedPlan.value,
    });
    activePayment.value = response.data.payment;
    qrContent.value = response.data.qr_content;
    await fetchPaymentHistory();
    beginPolling();
  } catch (e) {
    err.value = e?.response?.data?.message || "Không tạo được giao dịch.";
  } finally {
    loading.value = false;
  }
}

async function completePayment() {
  if (!activePayment.value?.id) return;
  completePaymentLoading.value = true;
  err.value = null;
  try {
    const response = await api.completePayment(activePayment.value.id);
    activePayment.value = response.data.payment;
    await fetchPaymentHistory();
  } catch (e) {
    if (e?.response?.status === 429) {
      const retry = e?.response?.data?.retry_after_seconds;
      if (typeof retry === 'number') {
        err.value = e?.response?.data?.message || 'Vui lòng đợi trước khi resend.';
      } else {
        err.value = e?.response?.data?.message || 'Không gửi được yêu cầu hoàn tất.';
      }
      return;
    }
    err.value = e?.response?.data?.message || "Không gửi được yêu cầu hoàn tất.";
  } finally {
    completePaymentLoading.value = false;
  }
}

const manualReviewRemainingSeconds = computed(() => {
  if (!activePayment.value) return 0;
  if (activePayment.value.status === 'paid') return 0;
  if (activePayment.value.manual_review_status !== 'requested') return 0;
  if (!activePayment.value.manual_review_requested_at) return 0;

  const requestedAtMs = new Date(activePayment.value.manual_review_requested_at).getTime();
  const nextAllowedMs = requestedAtMs + 5 * 60 * 1000;
  const diffMs = nextAllowedMs - nowTick.value;
  return Math.max(0, Math.ceil(diffMs / 1000));
});

const manualReviewCanResend = computed(() => manualReviewRemainingSeconds.value === 0);

const manualReviewCountdownText = computed(() => {
  const s = manualReviewRemainingSeconds.value;
  const mm = String(Math.floor(s / 60)).padStart(2, '0');
  const ss = String(s % 60).padStart(2, '0');
  return `${mm}:${ss}`;
});

const paymentStatusLabel = computed(() => {
  if (!activePayment.value) return '';
  if (activePayment.value.status === 'paid') return 'Đã thanh toán';
  if (activePayment.value.manual_review_status === 'requested') return 'Đã gửi yêu cầu hoàn tất';
  return 'Chờ thanh toán';
});

const startCountdown = () => {
  if (countdownTimer.value) {
    clearInterval(countdownTimer.value);
    countdownTimer.value = null;
  }

  if (!activePayment.value) return;
  if (activePayment.value.status === 'paid') return;
  if (activePayment.value.manual_review_status !== 'requested') return;

  countdownTimer.value = setInterval(() => {
    nowTick.value = Date.now();
    if (manualReviewRemainingSeconds.value === 0) {
      clearInterval(countdownTimer.value);
      countdownTimer.value = null;
    }
  }, 1000);
};

watch(
  () => [activePayment.value?.manual_review_status, activePayment.value?.manual_review_requested_at, activePayment.value?.status],
  () => {
    nowTick.value = Date.now();
    startCountdown();
  },
  { immediate: true }
);

function beginPolling() {
  stopPolling();
  pollTimer.value = setInterval(async () => {
    if (!activePayment.value?.id) return;
    const response = await api.getPaymentStatus(activePayment.value.id);
    activePayment.value = response.data;
    if (response.data.status === "paid") {
      stopPolling();
      await authStore.fetchMe();
      await getVipPredictions();
      await getVipYesterdayPredictions();
      await fetchPaymentHistory();
    }
  }, 5000);
}

onMounted(async () => {
  if (!authStore.isAuthenticated) return;
  await fetchPlans();
  await fetchUpsell();
  await fetchPaymentHistory();
  if (isVip.value) {
    await getVipPredictions();
    await getVipYesterdayPredictions();
  }
});

onUnmounted(() => stopPolling());
onUnmounted(() => {
  if (countdownTimer.value) {
    clearInterval(countdownTimer.value);
    countdownTimer.value = null;
  }
});
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <!-- Trial/Expired Banner -->
    <div
      v-if="isTrial"
      class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl p-6 text-white"
    >
      <div class="flex items-center gap-4">
        <div class="text-5xl">🔥</div>
        <div>
          <h1 class="text-2xl font-bold">Bạn đang dùng VIP Trial!</h1>
          <p class="mt-2 opacity-90">
            Còn {{ vipStatus?.vip_remaining_days }} ngày để trải nghiệm tất cả tính năng
            VIP. Nâng cấp để tiếp tục!
          </p>
        </div>
      </div>
    </div>

    <div v-if="!isVip || isNearExpired" class="bg-white rounded-xl border p-6 max-sm:p-3">
      <h1 class="text-2xl font-bold">
        {{
          isNearExpired
            ? isTrial
              ? "Trial sắp hết - Nâng cấp ngay!"
              : "Gia hạn VIP"
            : "Nâng cấp VIP"
        }}
      </h1>
      <p class="text-gray-600 mt-2">
        Trải nghiệm đầy đủ tính năng VIP để tăng tỷ lệ trúng.
      </p>
    </div>

    <!-- Benefits Section -->
    <div v-if="upsellData?.benefits" class="bg-white rounded-xl border p-6 max-sm:p-3">
      <h2 class="font-semibold mb-4">Quyền lợi VIP</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="(benefit, index) in upsellData.benefits"
          :key="index"
          class="flex items-center gap-2 text-sm"
        >
          <span class="text-green-500">✓</span>
          <span>{{ benefit }}</span>
        </div>
      </div>
    </div>

    <!-- Payment Section -->
    <div v-if="!isVip || isNearExpired" class="grid md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl border p-6 max-sm:p-3">
        <h2 class="font-semibold mb-4">Chọn gói VIP</h2>
        <div class="space-y-3">
          <label
            v-for="(plan, key) in plans"
            :key="key"
            class="flex justify-between items-center border rounded-lg p-3 cursor-pointer hover:border-yellow-400 transition"
          >
            <div class="flex items-center gap-3">
              <input
                v-model="selectedPlan"
                type="radio"
                :value="key"
                class="text-yellow-500"
              />
              <span>{{ plan.name }}</span>
            </div>
            <strong class="text-yellow-600"
              >{{ Number(plan.amount).toLocaleString("vi-VN") }}đ</strong
            >
          </label>
        </div>
        <button
          class="mt-4 w-full bg-yellow-500 text-black py-2 rounded-lg font-semibold disabled:opacity-50 hover:bg-yellow-400 transition"
          :disabled="loading || !selectedPlanInfo"
          @click="startVipPayment"
        >
          {{ loading ? "Đang xử lý..." : "Tạo mã QR thanh toán" }}
        </button>
        <p v-if="err" class="text-red-500 mt-3 text-sm">{{ err }}</p>
      </div>

      <div class="bg-white rounded-xl border p-6 max-sm:p-3" v-if="activePayment">
        <h2 class="font-semibold mb-3">Thông tin chuyển khoản</h2>
        <img v-if="qrUrl" :src="qrUrl" class="w-64 h-64 border mx-auto rounded-lg" />
        <div class="mt-4 text-sm space-y-1">
          <p><strong>Ngân hàng:</strong> {{ activePayment.bank_name }}</p>
          <p><strong>Số TK:</strong> {{ activePayment.bank_account_number }}</p>
          <p><strong>Chủ TK:</strong> {{ activePayment.bank_account_name }}</p>
          <p>
            <strong>Nội dung:</strong>
            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{
              activePayment.transfer_content
            }}</span>
          </p>
          <p>
            <strong>Tình trạng:</strong>
            <span
              :class="
                activePayment.status === 'paid'
                  ? 'text-green-600 font-semibold'
                  : activePayment.manual_review_status === 'requested'
                  ? 'text-blue-600 font-semibold'
                  : 'text-yellow-600'
              "
            >
              {{ paymentStatusLabel }}
            </span>
          </p>
        </div>
        <span class="text-sm text-gray-600 italic"
          >Lưu ý: Hãy giữ lại hình ảnh chuyển khoản thành công, để tiện xử lý nếu có bất
          kỳ lỗi nào xảy ra trong quá trình thanh toán!</span
        >

        <div v-if="activePayment.status !== 'paid' && activePayment.manual_review_status === 'requested'" class="mt-4 space-y-2">
          <div class="text-sm text-gray-600">
            Có thể gửi lại sau: <span class="font-mono">{{ manualReviewCountdownText }}</span>
          </div>
          <button
            class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold disabled:opacity-50 hover:bg-blue-500 transition"
            :disabled="completePaymentLoading || !manualReviewCanResend"
            @click="completePayment"
          >
            {{ completePaymentLoading ? "Đang gửi..." : "Resend phiếu yêu cầu hoàn tất" }}
          </button>
        </div>

        <button
          v-else
          class="mt-4 w-full bg-yellow-500 text-black py-2 rounded-lg font-semibold disabled:opacity-50 hover:bg-yellow-400 transition"
          :disabled="completePaymentLoading || activePayment.status !== 'pending'"
          @click="completePayment"
        >
          {{ completePaymentLoading ? "Đang gửi yêu cầu..." : "Hoàn tất thanh toán" }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl border p-6 max-sm:p-3">
      <h2 class="font-semibold mb-3">Lịch sử chuyển khoản (3 ngày gần nhất)</h2>
      <div v-if="!paymentHistory.length" class="text-sm text-gray-500">
        Chưa có giao dịch nào trong 3 ngày gần đây.
      </div>
      <div v-else class="space-y-2">
        <div
          v-for="item in paymentHistory"
          :key="item.id"
          class="border rounded-lg p-3 text-sm flex items-center justify-between gap-2"
        >
          <div>
            <p>
              <strong>{{ item.plan_name }}</strong> -
              {{ Number(item.amount).toLocaleString("vi-VN") }}đ
            </p>
            <p class="text-gray-600">
              Nội dung: <span class="font-mono">{{ item.transfer_content }}</span>
            </p>
          </div>
          <div class="text-right">
            <p class="font-medium">{{ item.status }}</p>
            <p class="text-gray-500">
              {{ formatDate(item.created_at, true, "DD-MM-YYYY HH:mm:ss") }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- VIP Content -->
    <div
      v-if="isVip"
      class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 max-sm:p-3"
    >
      <div class="flex items-center gap-3 mb-4">
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

      <PredictionCard class="mt-6" :data="predictions" />
      <YesterdayPredictionHits
        class="mt-6"
        v-if="yesterdayPredictions"
        :data="yesterdayPredictions"
      />

      <div
        v-if="predictions?.is_vip && predictions?.heatmap"
        class="mt-6 space-y-4"
      >
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

              <div
                class="z-50 bg-white rounded-lg shadow-2xl p-3 border border-gray-200"
              >
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
                    {{ Math.round(item.confidence) }}% Confidence
                  </span>
                  <span
                    v-if="item.is_in_prediction"
                    class="ml-1 inline-block px-2 py-1 rounded text-xs font-bold bg-purple-100 text-purple-700"
                  >
                    ✨ Trong dự đoán
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Tổng số lần ra (lô):</p>
                    <p class="font-bold text-gray-800">{{ item.total_count }}</p>
                  </div>
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Tổng số lần ra (đề):</p>
                    <p class="font-bold text-gray-800">{{ item.total_count_db }}</p>
                  </div>
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Gan hiện tại (lô):</p>
                    <p class="font-bold text-gray-800">{{ item.current_gap }}</p>
                  </div>
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Gan max (lô):</p>
                    <p class="font-bold text-gray-800">{{ item.max_gap }}</p>
                  </div>
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Gan hiện tại (đề):</p>
                    <p class="font-bold text-gray-800">{{ item.current_gap_db }}</p>
                  </div>
                  <div class="bg-gray-50 rounded p-2">
                    <p class="text-gray-500">Gan max (đề):</p>
                    <p class="font-bold text-gray-800">{{ item.max_gap_db }}</p>
                  </div>
                </div>

                <div class="mt-2 space-y-1">
                  <div v-if="item.last_hit_date" class="flex justify-between text-xs">
                    <span class="text-gray-500">Lần cuối ra lô:</span>
                    <span class="font-mono text-gray-700">{{
                      formatDate(item.last_hit_date)
                    }}</span>
                  </div>
                  <div v-if="item.last_db_hit_date" class="flex justify-between text-xs">
                    <span class="text-gray-500">Lần cuối ra đề:</span>
                    <span class="font-mono text-gray-700">{{
                      formatDate(item.last_db_hit_date)
                    }}</span>
                  </div>
                </div>

                <div
                  v-if="item.signals"
                  class="mt-2 pt-2 border-t border-gray-100 space-y-1"
                >
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Z-Score:</span>
                    <span class="font-mono text-gray-700">{{
                      (item.signals.z_score ?? 0).toFixed(2)
                    }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Trend:</span>
                    <span class="font-mono text-gray-700">{{
                      (item.signals.trend ?? 0).toFixed(2)
                    }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Gap Ratio:</span>
                    <span class="font-mono text-gray-700">{{
                      (item.signals.gap_ratio ?? 0).toFixed(2)
                    }}</span>
                  </div>
                </div>

                <p v-if="item.label" class="text-xs font-semibold text-gray-800 mt-2">
                  {{ item.label }}
                </p>
                <p v-if="item.message" class="text-xs text-gray-600">
                  {{ item.message }}
                </p>
              </div>
            </BasePopover>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
