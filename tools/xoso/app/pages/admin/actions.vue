<script setup>
definePageMeta({
  middleware: ['admin-only'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin - Actions',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const toLocalYmd = (d) => {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const startDate = ref(toLocalYmd(new Date()));
const endDate = ref(startDate.value);

const runPipeline = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  loading.value = true;
  try {
    const res = await api.adminRunDailyPipeline({ start_date: startDate.value, end_date: endDate.value });
    successMessage.value = res.data?.message || 'Đã chạy pipeline.';
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Chạy pipeline thất bại.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Actions</h1>
        <p class="text-sm text-gray-600 mt-1">
          Chạy chuỗi job theo thứ tự: crawl-result → update-stats → generate-prediction.
        </p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100 space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <div class="space-y-1">
          <div class="text-xs font-semibold text-gray-700">Khoảng ngày crawl</div>
          <div class="grid grid-cols-1 gap-2">
            <div class="flex gap-2 items-center">
              <div class="text-xs text-gray-600 w-20">Từ ngày</div>
              <input v-model="startDate" type="date" class="border rounded-lg px-3 py-2 text-sm w-full" />
            </div>
            <div class="flex gap-2 items-center">
              <div class="text-xs text-gray-600 w-20">Đến ngày</div>
              <input v-model="endDate" type="date" class="border rounded-lg px-3 py-2 text-sm w-full" />
            </div>
          </div>
        </div>
        <div class="md:col-span-2">
          <button
            class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
            :disabled="loading"
            @click="runPipeline"
          >
            {{ loading ? 'Đang chạy...' : 'Chạy pipeline' }}
          </button>
          <div class="text-xs text-gray-500 mt-2">
            Lưu ý: các job chạy trong queue. Cần worker đang chạy để xử lý.
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</div>
      <div v-if="successMessage" class="text-sm text-green-700">{{ successMessage }}</div>
    </div>
  </div>
</template>
