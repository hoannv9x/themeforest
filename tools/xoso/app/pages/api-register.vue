<script setup>
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Đăng ký API',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();
const authStore = useAuthStore();

const plans = ref({});
const selectedPlan = ref("api_30d");
const payment = ref(null);
const paymentHistory = ref([]);
const qrContent = ref("");
const subscription = ref(null);
const webhooks = ref([]);
const completePaymentLoading = ref(false);
const err = ref(null);
const countdownTimer = ref(null);
const nowTick = ref(Date.now());
const webhookForm = ref({
  name: "",
  url: "",
  event: "result.updated",
});

const qrUrl = computed(() =>
  qrContent.value
    ? `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(qrContent.value)}`
    : ""
);

const loadData = async () => {
  const planRes = await api.getPaymentPlans();
  plans.value = planRes.data.api || {};
  const [subscriptionRes, webhookRes] = await Promise.all([
    api.getApiSubscription(),
    api.getApiWebhooks(),
  ]);
  subscription.value = subscriptionRes.data;
  webhooks.value = webhookRes.data;
  const paymentHistoryRes = await api.getPaymentHistory();
  paymentHistory.value = paymentHistoryRes.data || [];
};

const createApiPayment = async () => {
  const response = await api.createPayment({ type: "api", plan_key: selectedPlan.value });
  payment.value = response.data.payment;
  qrContent.value = response.data.qr_content;
  await loadData();
};

const completePayment = async () => {
  if (!payment.value?.id) return;
  completePaymentLoading.value = true;
  err.value = null;
  try {
    const response = await api.completePayment(payment.value.id);
    payment.value = response.data.payment;
    await loadData();
  } catch (e) {
    if (e?.response?.status === 429) {
      err.value = e?.response?.data?.message || 'Vui lòng đợi trước khi resend.';
      return;
    }
    err.value = e?.response?.data?.message || e?.message || 'Không gửi được yêu cầu hoàn tất.';
  } finally {
    completePaymentLoading.value = false;
  }
};

const manualReviewRemainingSeconds = computed(() => {
  if (!payment.value) return 0;
  if (payment.value.status === 'paid') return 0;
  if (payment.value.manual_review_status !== 'requested') return 0;
  if (!payment.value.manual_review_requested_at) return 0;

  const requestedAtMs = new Date(payment.value.manual_review_requested_at).getTime();
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
  if (!payment.value) return '';
  if (payment.value.status === 'paid') return 'Đã thanh toán';
  if (payment.value.manual_review_status === 'requested') return 'Đã gửi yêu cầu hoàn tất';
  return 'Chờ thanh toán';
});

const startCountdown = () => {
  if (countdownTimer.value) {
    clearInterval(countdownTimer.value);
    countdownTimer.value = null;
  }

  if (!payment.value) return;
  if (payment.value.status === 'paid') return;
  if (payment.value.manual_review_status !== 'requested') return;

  countdownTimer.value = setInterval(() => {
    nowTick.value = Date.now();
    if (manualReviewRemainingSeconds.value === 0) {
      clearInterval(countdownTimer.value);
      countdownTimer.value = null;
    }
  }, 1000);
};

watch(
  () => [payment.value?.manual_review_status, payment.value?.manual_review_requested_at, payment.value?.status],
  () => {
    nowTick.value = Date.now();
    startCountdown();
  },
  { immediate: true }
);

onUnmounted(() => {
  if (countdownTimer.value) {
    clearInterval(countdownTimer.value);
    countdownTimer.value = null;
  }
});

const createWebhook = async () => {
  await api.createApiWebhook(webhookForm.value);
  webhookForm.value = { name: "", url: "", event: "result.updated" };
  await loadData();
};

const toggleWebhook = async (item) => {
  await api.updateApiWebhook(item.id, { is_active: !item.is_active });
  await loadData();
};

const removeWebhook = async (id) => {
  await api.deleteApiWebhook(id);
  await loadData();
};

onMounted(async () => {
  if (!authStore.isAuthenticated) return;
  await loadData();
});
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <section class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">Đăng ký sử dụng API</h1>
      <p class="text-gray-600 mt-2">
        Gọi API kết quả, thống kê, nhận webhook realtime khi có kết quả mới.
      </p>
      <p class="mt-3 text-sm">
        Permission hiện tại:
        <strong>{{ authStore.user?.permission || "user" }}</strong>
      </p>
      <p v-if="subscription" class="text-sm mt-1">
        Gói hiện tại: <strong>{{ subscription.plan_name }}</strong> - hết hạn
        <strong>{{ subscription.expires_at }}</strong>
      </p>
    </section>

    <section class="grid md:grid-cols-2 gap-6">
      <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold mb-3">Chọn gói sử dụng API</h2>
        <div class="space-y-2">
          <label v-for="(plan, key) in plans" :key="key" class="flex justify-between border rounded p-3">
            <div class="flex items-center gap-2">
              <input v-model="selectedPlan" type="radio" :value="key" />
              <span>{{ plan.name }}</span>
            </div>
            <strong>{{ Number(plan.amount).toLocaleString("vi-VN") }}d</strong>
          </label>
        </div>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded" @click="createApiPayment">
          Tạo thanh toán gói sử dụng API
        </button>
      </div>

      <div v-if="payment" class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold">Quét mã QR</h2>
        <img v-if="qrUrl" :src="qrUrl" class="w-64 h-64 border rounded mt-3" />
        <p class="text-sm mt-3"><strong>Nội dung:</strong> {{ payment.transfer_content }}</p>
        <p class="text-sm mt-2">
          <strong>Tình trạng:</strong>
          <span
            :class="
              payment.status === 'paid'
                ? 'text-green-600 font-semibold'
                : payment.manual_review_status === 'requested'
                ? 'text-blue-600 font-semibold'
                : 'text-yellow-600'
            "
          >
            {{ paymentStatusLabel }}
          </span>
        </p>

        <p v-if="err" class="text-red-600 text-sm mt-2">{{ err }}</p>

        <div v-if="payment.status !== 'paid' && payment.manual_review_status === 'requested'" class="mt-4 space-y-2">
          <div class="text-sm text-gray-600">
            Có thể gửi lại sau: <span class="font-mono">{{ manualReviewCountdownText }}</span>
          </div>
          <button
            class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50 w-full"
            :disabled="completePaymentLoading || !manualReviewCanResend"
            @click="completePayment"
          >
            {{ completePaymentLoading ? "Đang gửi..." : "Resend phiếu yêu cầu hoàn tất" }}
          </button>
        </div>

        <button
          v-else
          class="mt-4 bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50 w-full"
          :disabled="completePaymentLoading || payment.status !== 'pending'"
          @click="completePayment"
        >
          {{ completePaymentLoading ? "Đang gửi..." : "Hoàn tất thanh toán" }}
        </button>
      </div>
    </section>

    <section class="bg-white border rounded-xl p-6">
      <h2 class="text-lg font-semibold">Lịch sử chuyển khoản (3 ngày gần nhất)</h2>
      <div v-if="!paymentHistory.length" class="text-sm text-gray-500 mt-2">
        Chưa có giao dịch nào trong 3 ngày gần đây.
      </div>
      <div v-else class="mt-3 space-y-2">
        <div
          v-for="item in paymentHistory"
          :key="item.id"
          class="border rounded p-3 flex justify-between text-sm"
        >
          <div>
            <p class="font-medium">{{ item.plan_name }}</p>
            <p class="text-gray-600">
              {{ Number(item.amount).toLocaleString("vi-VN") }}đ - {{ item.transfer_content }}
            </p>
          </div>
          <p class="font-medium">{{ item.status }}</p>
        </div>
      </div>
    </section>

    <section class="bg-white border rounded-xl p-6">
      <h2 class="text-lg font-semibold">Webhook quản lý theo user</h2>
      <div class="grid md:grid-cols-3 gap-3 mt-4">
        <input v-model="webhookForm.name" placeholder="Tên webhook" class="border rounded px-3 py-2" />
        <input v-model="webhookForm.url" placeholder="https://domain.com/webhook" class="border rounded px-3 py-2 md:col-span-2" />
      </div>
      <button class="mt-3 bg-green-600 text-white px-4 py-2 rounded" @click="createWebhook">
        Thêm webhook
      </button>

      <div class="mt-4 space-y-3">
        <div v-for="item in webhooks" :key="item.id" class="border rounded p-3 flex justify-between">
          <div>
            <p class="font-medium">{{ item.name }}</p>
            <p class="text-sm text-gray-600">{{ item.url }}</p>
            <p class="text-xs text-gray-500">Last: {{ item.last_status_code || "-" }}</p>
          </div>
          <div class="space-x-2">
            <button class="px-3 py-1 border rounded" @click="toggleWebhook(item)">
              {{ item.is_active ? "Pause" : "Enable" }}
            </button>
            <button class="px-3 py-1 border rounded text-red-600" @click="removeWebhook(item.id)">Xóa</button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
