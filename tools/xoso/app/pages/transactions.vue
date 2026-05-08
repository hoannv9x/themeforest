<script setup>
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Lịch sử giao dịch',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const { formatDate } = useFormatters();
const api = useApi();
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

const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const items = ref([]);
const cancellingId = ref(null);

const fetchHistory = async () => {
  loading.value = true;
  errorMessage.value = "";
  successMessage.value = "";
  try {
    const res = await api.getPaymentHistory();
    items.value = res.data || [];
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || "Không tải được lịch sử giao dịch.";
    items.value = [];
  } finally {
    loading.value = false;
  }
};

const cancelPayment = async (row) => {
  if (!row?.id) return;
  errorMessage.value = "";
  successMessage.value = "";
  cancellingId.value = row.id;
  try {
    const res = await api.cancelPayment(row.id);
    successMessage.value = res.data?.message || "Đã huỷ giao dịch.";
    await fetchHistory();
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || "Không huỷ được giao dịch.";
  } finally {
    cancellingId.value = null;
  }
};

const statusClass = (row) => {
  if (row.status === "paid") return "bg-green-100 text-green-700";
  if (row.status === "pending") return "bg-yellow-100 text-yellow-700";
  if (row.status === "cancelled") return "bg-gray-100 text-gray-700";
  if (row.status === "rejected") return "bg-red-100 text-red-700";
  if (row.status === "failed") return "bg-red-100 text-red-700";
  if (row.status === "expired") return "bg-gray-100 text-gray-700";
  return "bg-gray-100 text-gray-700";
};

onMounted(fetchHistory);
</script>

<template>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">Lịch sử giao dịch</h1>
      <p class="text-gray-600 mt-2">Danh sách giao dịch (3 ngày gần nhất).</p>
    </div>

    <div class="bg-white border rounded-xl p-6">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600" v-if="!loading">
          Tổng: <strong>{{ items.length }}</strong>
        </div>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="fetchHistory"
        >
          {{ loading ? "Đang tải..." : "Tải lại" }}
        </button>
      </div>

      <div v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</div>
      <div v-if="successMessage" class="mt-3 text-sm text-green-700">{{ successMessage }}</div>

      <div v-if="!loading && !items.length" class="text-sm text-gray-500 mt-3">
        Chưa có giao dịch nào trong 3 ngày gần đây.
      </div>

      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">ID</th>
              <th class="text-left px-4 py-2">Loại</th>
              <th class="text-left px-4 py-2">Gói</th>
              <th class="text-left px-4 py-2">Số tiền</th>
              <th class="text-left px-4 py-2">Trạng thái</th>
              <th class="text-left px-4 py-2">Thời gian</th>
              <th class="text-left px-4 py-2">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in items" :key="row.id" class="border-t">
              <td class="px-4 py-2 font-mono text-xs">{{ row.id }}</td>
              <td class="px-4 py-2">{{ row.type }}</td>
              <td class="px-4 py-2">{{ row.plan_name }}</td>
              <td class="px-4 py-2">{{ Number(row.amount).toLocaleString("vi-VN") }}đ</td>
              <td class="px-4 py-2">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(row)">
                  {{ row.status }}
                </span>
              </td>
              <td class="px-4 py-2 text-gray-600">
                {{ formatDate(row.created_at, true, "DD-MM-YYYY HH:mm:ss") }}
              </td>
              <td class="px-4 py-2">
                <button
                  v-if="row.status === 'pending'"
                  class="border rounded-lg px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"
                  :disabled="loading || cancellingId === row.id"
                  @click="cancelPayment(row)"
                >
                  {{ cancellingId === row.id ? "Đang huỷ..." : "Huỷ" }}
                </button>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

