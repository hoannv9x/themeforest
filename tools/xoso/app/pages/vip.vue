<script setup>
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
const predictions = ref(null);
const yesterdayPredictions = ref(null);
const err = ref(null);
const completePaymentLoading = ref(false);

const isVip = computed(() => authStore?.user?.role === "vip");
const vipExpiredAt = computed(() => authStore?.user?.vip_expired_at);
const isNearExpired = computed(() => {
  if (!vipExpiredAt.value) return false;
  return new Date(vipExpiredAt.value).getTime() - Date.now() < 1000 * 60 * 60 * 24 * 3;
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
    err.value = e?.response?.data?.message || "Không gửi được yêu cầu hoàn tất.";
  } finally {
    completePaymentLoading.value = false;
  }
}

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
    }
  }, 5000);
}

// async function markPaymentPaid() {
//   await api.markPaymentPaid({
//     transfer_content: activePayment.value.transfer_content,
//     amount: activePayment.value.amount,
//     bank_ref: activePayment.value.bank_ref,
//     secret: activePayment.value.secret,
//   });
// }

onMounted(async () => {
  if (!authStore.isAuthenticated) return;
  await fetchPlans();
  await fetchPaymentHistory();
  if (isVip.value) {
    await getVipPredictions();
    await getVipYesterdayPredictions();
  }
});

onUnmounted(() => stopPolling());
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="bg-white rounded-xl border p-6" v-if="!isVip || isNearExpired">
      <h1 class="text-2xl font-bold">
        {{ isNearExpired ? "Gia hạn VIP" : "Nang cap VIP" }}
      </h1>
      <p class="text-gray-600 mt-2">
        Quét QR và chuyển khoản dung nội dung để hệ thống tự động xác nhận giao dịch.
      </p>
    </div>

    <div v-if="!isVip || isNearExpired" class="grid md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl border p-6">
        <h2 class="font-semibold mb-4">Chọn gói VIP</h2>
        <div class="space-y-3">
          <label
            v-for="(plan, key) in plans"
            :key="key"
            class="flex justify-between items-center border rounded-lg p-3 cursor-pointer"
          >
            <div class="flex items-center gap-3">
              <input v-model="selectedPlan" type="radio" :value="key" />
              <span>{{ plan.name }}</span>
            </div>
            <strong>{{ Number(plan.amount).toLocaleString("vi-VN") }}d</strong>
          </label>
        </div>
        <button
          class="mt-4 w-full bg-yellow-500 text-black py-2 rounded-lg font-semibold disabled:opacity-50"
          :disabled="loading || !selectedPlanInfo"
          @click="startVipPayment"
        >
          Tạo mã QR thanh toán
        </button>
        <p v-if="err" class="text-red-500 mt-3 text-sm">{{ err }}</p>
      </div>

      <div class="bg-white rounded-xl border p-6" v-if="activePayment">
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
          <p><strong>Tình trạng:</strong> {{ activePayment.status }}</p>
        </div>
        <span class="text-sm text-gray-600 italic"
          >Lưu ý: Hãy giữ lại hình ảnh chuyển khoản thành công, để tiện xử lý nếu có bất
          kỳ lỗi nào xảy ra trong quá trình thanh toán!</span
        >
        <button
          class="mt-4 w-full bg-yellow-500 text-black py-2 rounded-lg font-semibold disabled:opacity-50"
          :disabled="completePaymentLoading || activePayment.status !== 'pending'"
          @click="completePayment"
        >
          {{
            completePaymentLoading
              ? "Đang gửi yêu cầu..."
              : "Hoàn tất thanh toán"
          }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl border p-6">
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
            <p><strong>{{ item.plan_name }}</strong> - {{ Number(item.amount).toLocaleString("vi-VN") }}đ</p>
            <p class="text-gray-600">Nội dung: <span class="font-mono">{{ item.transfer_content }}</span></p>
          </div>
          <div class="text-right">
            <p class="font-medium">{{ item.status }}</p>
            <p class="text-gray-500">{{ formatDate(item.created_at, true, 'DD-MM-YYYY HH:mm:ss') }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isVip" class="bg-green-50 border border-green-200 rounded-xl p-6">
      <h2 class="text-lg font-semibold text-green-700">Ban dang la VIP</h2>
      <p class="mt-1">Hết hạn: {{ formatDate(vipExpiredAt, true, 'DD-MM-YYYY HH:mm:ss') }}</p>
      <PredictionCard class="mt-6" :data="predictions" />
      <YesterdayPredictionHits
        class="mt-6"
        v-if="yesterdayPredictions"
        :data="yesterdayPredictions"
      />
    </div>
  </div>
</template>
