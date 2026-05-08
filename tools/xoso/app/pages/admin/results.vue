<script setup>
definePageMeta({
  middleware: ['admin'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin - Results',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const region = ref('mb');
const toLocalYmd = (d) => {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const date = ref(toLocalYmd(new Date()));

const result = ref(null);

const recentLoading = ref(false);
const recentResults = ref([]);
const recentPagination = ref(null);
const recentPage = ref(1);

const prizeOrder = [
  'ma_db',
  'db',
  'g1',
  'g2',
  'g3',
  'g4',
  'g5',
  'g6',
  'g7',
];

const fields = reactive(
  prizeOrder.reduce((acc, k) => {
    acc[k] = '';
    return acc;
  }, {})
);

const resetMessages = () => {
  errorMessage.value = '';
  successMessage.value = '';
};

const normalizeText = (value) => {
  if (Array.isArray(value)) return value.join(' - ');
  if (value === null || value === undefined) return '';
  return String(value);
};

const parsePrize = (text) => {
  const parts = String(text || '')
    .split(/\r?\n|-/g)
    .map((s) => s.trim())
    .filter((s) => s.length > 0);
  return parts;
};

const loadResult = async () => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminGetResultByDate(date.value, { region: region.value });
    result.value = res.data?.result || null;

    const raw = result.value?.raw_data || {};
    for (const key of prizeOrder) {
      fields[key] = normalizeText(raw?.[key] || []);
    }

    successMessage.value = result.value ? 'Đã tải kết quả.' : 'Chưa có kết quả cho ngày này. Có thể nhập và lưu mới.';
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được result.';
  } finally {
    loading.value = false;
  }
};

const loadRecent = async () => {
  recentLoading.value = true;
  try {
    const res = await api.adminGetResults({ region: region.value, page: recentPage.value });
    recentResults.value = res.data?.data || [];
    recentPagination.value = res.data || null;
  } finally {
    recentLoading.value = false;
  }
};

const openRecent = async (row) => {
  const d = String(row?.date || '').slice(0, 10);
  if (d) {
    date.value = d;
  }
  await loadResult();
};

const recentPrev = () => {
  const current = recentPagination.value?.current_page || recentPage.value;
  if (current > 1) {
    recentPage.value = current - 1;
  }
};

const recentNext = () => {
  const current = recentPagination.value?.current_page || recentPage.value;
  const last = recentPagination.value?.last_page || 1;
  if (current < last) {
    recentPage.value = current + 1;
  }
};

const saveResult = async () => {
  resetMessages();
  loading.value = true;
  try {
    const raw_data = {};
    for (const key of prizeOrder) {
      raw_data[key] = parsePrize(fields[key]);
    }

    const payload = {
      region: region.value,
      province_code: 'MB_TRADITION',
      raw_data,
    };

    const res = await api.adminUpsertResultByDate(date.value, payload);
    successMessage.value = res.data?.message || 'Đã lưu result.';
    result.value = res.data?.result || null;
  } catch (e) {
    const msg = e?.response?.data?.message || e?.message || 'Lưu thất bại.';
    errorMessage.value = msg;
  } finally {
    loading.value = false;
  }
};

watch(region, async () => {
  recentPage.value = 1;
  await loadRecent();
});

watch(recentPage, loadRecent);

onMounted(async () => {
  await loadResult();
  await loadRecent();
});
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Result</h1>
        <p class="text-sm text-gray-600 mt-1">Sửa tay kết quả theo ngày khi crawl sai.</p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="space-y-1">
          <div class="text-xs font-semibold text-gray-700">Ngày</div>
          <input v-model="date" type="date" class="border rounded-lg px-3 py-2 text-sm w-full" />
        </div>
        <div class="space-y-1">
          <div class="text-xs font-semibold text-gray-700">Miền</div>
          <select v-model="region" class="border rounded-lg px-3 py-2 text-sm w-full">
            <option value="mb">mb</option>
            <option value="mn">mn</option>
            <option value="mt">mt</option>
          </select>
        </div>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="loadResult"
        >
          {{ loading ? 'Đang tải...' : 'Tải' }}
        </button>
        <button
          class="bg-green-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="saveResult"
        >
          {{ loading ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </div>

      <div v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</div>
      <div v-if="successMessage" class="mt-3 text-sm text-green-700">{{ successMessage }}</div>
      <div v-if="result" class="mt-3 text-xs text-gray-500">
        Result ID: <span class="font-mono text-gray-800">{{ result.id }}</span>
      </div>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="key in prizeOrder" :key="key" class="space-y-1">
          <div class="text-xs font-semibold text-gray-700">{{ key }}</div>
          <textarea
            v-model="fields[key]"
            rows="3"
            class="border rounded-lg px-3 py-2 text-sm w-full font-mono"
            placeholder="Nhập các số, có thể phân tách bằng dấu - hoặc xuống dòng"
          />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <div class="text-sm font-semibold text-gray-900">Danh sách gần đây</div>
        <div class="text-xs text-gray-500">
          Trang {{ recentPagination?.current_page || recentPage }} / {{ recentPagination?.last_page || 1 }}
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">Ngày</th>
              <th class="text-left px-4 py-2">Miền</th>
              <th class="text-left px-4 py-2">Province</th>
              <th class="text-right px-4 py-2">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in recentResults" :key="r.id" class="border-t">
              <td class="px-4 py-2 font-mono">{{ String(r.date).slice(0, 10) }}</td>
              <td class="px-4 py-2">{{ r.region }}</td>
              <td class="px-4 py-2">{{ r.province_code }}</td>
              <td class="px-4 py-2 text-right">
                <button
                  class="text-blue-600 hover:underline text-sm"
                  :disabled="recentLoading || loading"
                  @click="() => openRecent(r)"
                >
                  Mở
                </button>
              </td>
            </tr>
            <tr v-if="!recentResults.length">
              <td class="px-4 py-4 text-center text-gray-500" colspan="4">Không có dữ liệu</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-4 py-3 border-t flex items-center justify-between">
        <button
          class="text-sm px-3 py-2 rounded-lg border disabled:opacity-40"
          :disabled="recentLoading || (recentPagination?.current_page || recentPage) <= 1"
          @click="recentPrev"
        >
          ← Trước
        </button>
        <button
          class="text-sm px-3 py-2 rounded-lg border disabled:opacity-40"
          :disabled="recentLoading || (recentPagination?.current_page || recentPage) >= (recentPagination?.last_page || 1)"
          @click="recentNext"
        >
          Sau →
        </button>
      </div>
    </div>
  </div>
</template>
