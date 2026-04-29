<script setup>
import { useAuthStore } from "~/stores/auth";

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
  try {
    const response = await api.completePayment(payment.value.id);
    payment.value = response.data.payment;
    await loadData();
  } finally {
    completePaymentLoading.value = false;
  }
};

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
        <button
          class="mt-4 bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
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
