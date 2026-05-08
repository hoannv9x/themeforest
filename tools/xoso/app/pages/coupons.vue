<script setup>
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Coupon của tôi',
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
const items = ref([]);

const fetchCoupons = async () => {
  loading.value = true;
  errorMessage.value = "";
  try {
    const res = await api.getMyCoupons();
    items.value = res.data || [];
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || "Không tải được coupon.";
    items.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(fetchCoupons);
</script>

<template>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">Coupon của tôi</h1>
      <p class="text-gray-600 mt-2">Danh sách coupon còn hiệu lực.</p>
    </div>

    <div class="bg-white border rounded-xl p-6">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600" v-if="!loading">
          Tổng: <strong>{{ items.length }}</strong>
        </div>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="fetchCoupons"
        >
          {{ loading ? "Đang tải..." : "Tải lại" }}
        </button>
      </div>

      <div v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</div>

      <div v-if="!loading && !items.length" class="text-sm text-gray-500 mt-3">
        Bạn chưa có coupon nào còn hiệu lực.
      </div>

      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">Code</th>
              <th class="text-left px-4 py-2">Giảm</th>
              <th class="text-left px-4 py-2">Lượt dùng</th>
              <th class="text-left px-4 py-2">Hết hạn</th>
              <th class="text-left px-4 py-2">Nguồn</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in items" :key="row.id" class="border-t">
              <td class="px-4 py-2 font-mono">{{ row.code }}</td>
              <td class="px-4 py-2">{{ row.discount_percent }}%</td>
              <td class="px-4 py-2">{{ row.used_count }} / {{ row.max_uses ?? "∞" }}</td>
              <td class="px-4 py-2 text-gray-600">
                {{ row.expires_at ? formatDate(row.expires_at, true, "DD-MM-YYYY") : "Không" }}
              </td>
              <td class="px-4 py-2">{{ row.source }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

