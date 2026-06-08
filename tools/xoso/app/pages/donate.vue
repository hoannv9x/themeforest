<script setup>
import { useAuthStore } from "~/stores/auth";
const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Tán Lộc - XoSo AI',
  description: 'Chia sẻ niềm vui thắng lợi, tán lộc để may mắn tiếp tục song hành.',
  ogTitle: 'Tán Lộc - XoSo AI',
  ogDescription: 'Chia sẻ niềm vui thắng lợi, tán lộc để may mắn tiếp tục song hành.',
  ogUrl: canonical,
});

const api = useApi();
const authStore = useAuthStore();
const loading = ref(false);
const activePayment = ref(null);
const qrContent = ref("");
const err = ref(null);
const completePaymentLoading = ref(false);
const customAmount = ref("");
const selectedAmount = ref(10000);

const amounts = [
  { value: 1000, label: '1k' },
  { value: 2000, label: '2k' },
  { value: 5000, label: '5k' },
  { value: 10000, label: '10k' },
  { value: 20000, label: '20k' },
  { value: 50000, label: '50k' },
  { value: 100000, label: '100k' },
  { value: 200000, label: '200k' },
  { value: 500000, label: '500k' },
];

const qrUrl = computed(() => qrContent.value || "");

async function startDonation() {
  const amountToDonate = selectedAmount.value === 'custom' ? parseInt(customAmount.value) : selectedAmount.value;
  
  if (!amountToDonate || amountToDonate < 1000) {
    err.value = "Số tiền tối thiểu là 1,000đ";
    return;
  }

  loading.value = true;
  err.value = null;
  try {
    const response = await api.createPayment({
      type: "donate",
      amount: amountToDonate,
    });
    activePayment.value = response.data.payment;
    qrContent.value = response.data.qr_content;
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
  } catch (e) {
    err.value = e?.response?.data?.message || "Không gửi được yêu cầu hoàn tất.";
  } finally {
    completePaymentLoading.value = false;
  }
}

function reset() {
  activePayment.value = null;
  qrContent.value = "";
  err.value = null;
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">🧧 Tán Lộc May Mắn</h1>
      <p class="text-gray-600">Chia sẻ niềm vui thắng lợi, tán lộc để nhận thêm nhiều may mắn mới.</p>
    </div>

    <div v-if="!activePayment" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <div class="mb-8">
        <label class="block text-sm font-medium text-gray-700 mb-4">Chọn số tiền tán lộc:</label>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
          <button
            v-for="amt in amounts"
            :key="amt.value"
            @click="selectedAmount = amt.value"
            class="py-3 px-2 rounded-xl border-2 transition-all font-semibold"
            :class="selectedAmount === amt.value ? 'border-red-500 bg-red-50 text-red-600' : 'border-gray-100 hover:border-gray-200 text-gray-600'"
          >
            {{ amt.label }}
          </button>
          <button
            @click="selectedAmount = 'custom'"
            class="py-3 px-2 rounded-xl border-2 transition-all font-semibold"
            :class="selectedAmount === 'custom' ? 'border-red-500 bg-red-50 text-red-600' : 'border-gray-100 hover:border-gray-200 text-gray-600'"
          >
            Khác
          </button>
        </div>
      </div>

      <div v-if="selectedAmount === 'custom'" class="mb-8">
        <label class="block text-sm font-medium text-gray-700 mb-2">Nhập số tiền:</label>
        <div class="relative">
          <input
            v-model="customAmount"
            type="number"
            placeholder="Ví dụ: 88000"
            class="w-full pl-4 pr-12 py-3 rounded-xl border-2 border-gray-100 focus:border-red-500 outline-none transition-all"
          />
          <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">VNĐ</span>
        </div>
      </div>

      <div v-if="err" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-sm font-medium">
        {{ err }}
      </div>

      <button
        @click="startDonation"
        :disabled="loading"
        class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-300 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-red-200 transition-all flex items-center justify-center gap-2"
      >
        <span v-if="loading" class="animate-spin">🌀</span>
        {{ loading ? 'Đang tạo giao dịch...' : 'Xác nhận Tán Lộc' }}
      </button>
    </div>

    <!-- Payment View -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-lg mx-auto">
      <div v-if="activePayment.status === 'paid'" class="text-center py-8">
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
          ✓
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Tán Lộc Thành Công!</h2>
        <p class="text-gray-600 mb-8">Cảm ơn bạn đã chia sẻ niềm vui. Chúc bạn tiếp tục gặp nhiều may mắn!</p>
        <button @click="reset" class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold">Quay lại</button>
      </div>

      <div v-else>
        <div class="text-center mb-8">
          <h2 class="text-xl font-bold text-gray-900 mb-1">Quét mã QR để Tán Lộc</h2>
          <p class="text-gray-500 text-sm">Giao dịch sẽ được xử lý tự động sau khi chuyển khoản</p>
        </div>

        <div class="flex justify-center mb-8 bg-gray-50 p-6 rounded-2xl">
          <img :src="qrUrl" class="w-64 h-64 shadow-inner" />
        </div>

        <div class="space-y-4 mb-8">
          <div class="flex justify-between items-center py-3 border-b border-gray-50">
            <span class="text-gray-500 text-sm">Số tiền:</span>
            <span class="font-bold text-lg text-red-600">{{ activePayment.amount.toLocaleString() }}đ</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-gray-50">
            <span class="text-gray-500 text-sm">Nội dung chuyển:</span>
            <span class="font-mono font-bold bg-gray-100 px-2 py-1 rounded">{{ activePayment.transfer_content }}</span>
          </div>
          <div class="flex justify-between items-center py-3">
            <span class="text-gray-500 text-sm">Trạng thái:</span>
            <span class="text-yellow-600 font-semibold">{{ activePayment.manual_review_status === 'requested' ? 'Đang chờ duyệt' : 'Chờ thanh toán' }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <button
            v-if="activePayment.manual_review_status !== 'requested'"
            @click="completePayment"
            :disabled="completePaymentLoading"
            class="w-full bg-red-600 text-white py-4 rounded-xl font-bold shadow-lg shadow-red-100 disabled:bg-gray-300"
          >
            {{ completePaymentLoading ? 'Đang xử lý...' : 'Tôi đã chuyển khoản' }}
          </button>
          <button @click="reset" class="w-full bg-gray-100 text-gray-600 py-4 rounded-xl font-bold">Hủy giao dịch</button>
        </div>
      </div>
    </div>
  </div>
</template>