<script setup>
definePageMeta({
  middleware: ['admin-only'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin - Logs',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();

const loading = ref(false);
const errorMessage = ref('');

const files = ref([]);
const selectedDate = ref('');
const lines = ref(800);

const contentLoading = ref(false);
const contentError = ref('');
const content = ref('');
const truncated = ref(false);

const downloading = ref(false);

const fetchFiles = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await api.adminGetLogs();
    files.value = res.data?.items || [];
    if (!selectedDate.value && files.value.length) {
      selectedDate.value = files.value[0].date;
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được danh sách log.';
  } finally {
    loading.value = false;
  }
};

const fetchContent = async () => {
  if (!selectedDate.value) return;
  contentLoading.value = true;
  contentError.value = '';
  try {
    const res = await api.adminGetLogByDate(selectedDate.value, { lines: lines.value });
    content.value = res.data?.content || '';
    truncated.value = !!res.data?.truncated;
  } catch (e) {
    contentError.value = e?.response?.data?.message || e?.message || 'Không tải được nội dung log.';
    content.value = '';
    truncated.value = false;
  } finally {
    contentLoading.value = false;
  }
};

watch([selectedDate, lines], fetchContent);

const downloadLog = async () => {
  if (!selectedDate.value) return;
  downloading.value = true;
  try {
    const res = await api.adminDownloadLogByDate(selectedDate.value);
    const blob = new Blob([res.data], { type: res.headers?.['content-type'] || 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `laravel-${selectedDate.value}.log`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    contentError.value = e?.response?.data?.message || e?.message || 'Không download được file log.';
  } finally {
    downloading.value = false;
  }
};

onMounted(async () => {
  await fetchFiles();
  await fetchContent();
});
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Logs</h1>
        <p class="text-sm text-gray-600 mt-1">Xem nội dung file laravel-YYYY-MM-DD.log (giữ 3 ngày gần nhất).</p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          class="border rounded-lg px-3 py-2 text-sm font-semibold hover:bg-gray-50 disabled:opacity-50"
          :disabled="loading"
          @click="fetchFiles"
        >
          Refresh danh sách
        </button>

        <select v-model="selectedDate" class="border rounded-lg px-3 py-2 text-sm" :disabled="loading || !files.length">
          <option v-for="f in files" :key="f.date" :value="f.date">
            {{ f.date }} ({{ Math.round((f.size_bytes || 0) / 1024) }} KB)
          </option>
        </select>

        <select v-model.number="lines" class="border rounded-lg px-3 py-2 text-sm" :disabled="loading || !selectedDate">
          <option :value="200">200 lines</option>
          <option :value="800">800 lines</option>
          <option :value="2000">2000 lines</option>
        </select>

        <button
          type="button"
          class="border rounded-lg px-3 py-2 text-sm font-semibold hover:bg-gray-50 disabled:opacity-50"
          :disabled="contentLoading || !selectedDate"
          @click="fetchContent"
        >
          Refresh log
        </button>

        <button
          type="button"
          class="border rounded-lg px-3 py-2 text-sm font-semibold hover:bg-gray-50 disabled:opacity-50"
          :disabled="downloading || !selectedDate"
          @click="downloadLog"
        >
          {{ downloading ? 'Đang tải...' : 'Download' }}
        </button>

        <div v-if="truncated" class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-2 py-1">
          Nội dung đang hiển thị bản rút gọn (tail).
        </div>
      </div>

      <div v-if="contentError" class="text-sm text-red-600 mt-3">{{ contentError }}</div>

      <div class="mt-4">
        <div v-if="contentLoading" class="text-sm text-gray-600">Đang tải...</div>
        <pre
          v-else
          class="text-xs bg-gray-900 text-gray-100 rounded-lg p-4 overflow-auto max-h-[70vh] whitespace-pre-wrap"
        >{{ content || 'Không có dữ liệu.' }}</pre>
      </div>
    </div>
  </div>
</template>
