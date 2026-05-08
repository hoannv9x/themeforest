<script setup>
const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Kết quả xổ số',
  description: 'Xem kết quả xổ số theo ngày và theo miền. Cập nhật nhanh, dễ theo dõi.',
  ogTitle: 'Kết quả xổ số - XoSo AI',
  ogDescription: 'Xem kết quả xổ số theo ngày và theo miền. Cập nhật nhanh, dễ theo dõi.',
  ogUrl: canonical,
  twitterTitle: 'Kết quả xổ số - XoSo AI',
  twitterDescription: 'Xem kết quả xổ số theo ngày và theo miền. Cập nhật nhanh, dễ theo dõi.',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
});

const activeTab = ref("xsmb");
const api = useApi();
import { useAuthStore } from "~/stores/auth";
const authStore = useAuthStore();
authStore.initAuth();
const tabs = [
  { key: "xsmb", label: "XSMB" },
  // { key: "xsmn", label: "XSMN" },
  // { key: "xsmt", label: "XSMT" },
];

const loading = ref(false);
const xsmb = ref([]);
const pagination = ref(null);
const page = ref(1);

async function getResult() {
  loading.value = true;
  try {
    const fn = authStore.isVip ? api.getVipResults : api.getResults;
    const { data } = await fn({
      region: 'mb',
      include_predictions: true,
      page: page.value,
    });

    xsmb.value = data?.data || [];
    pagination.value = data || null;
  } catch (e) {
    console.log(e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  getResult();
});

watch(page, getResult);

const prevPage = () => {
  const current = pagination.value?.current_page || page.value;
  if (current > 1) page.value = current - 1;
};

const nextPage = () => {
  const current = pagination.value?.current_page || page.value;
  const last = pagination.value?.last_page || 1;
  if (current < last) page.value = current + 1;
};
</script>

<template>
  <div class="max-w-6xl mx-auto p-4">
    <!-- Title -->
    <h1 class="text-2xl font-bold text-center mb-6">Kết quả xổ số</h1>

    <!-- Tabs -->
    <div class="flex justify-center mb-6 space-x-2">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        :class="[
          'px-4 py-2 rounded border',
          activeTab === tab.key ? 'bg-red-500 text-white' : 'bg-white',
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- XSMB -->
    <div v-if="activeTab == 'xsmb'" class="bg-white rounded shadow p-4">
      <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">
          Trang {{ pagination?.current_page || page }} / {{ pagination?.last_page || 1 }}
        </div>
        <div class="flex gap-2">
          <button
            class="px-3 py-2 rounded border text-sm disabled:opacity-50"
            :disabled="loading || (pagination?.current_page || page) <= 1"
            @click="prevPage"
          >
            ← Trước
          </button>
          <button
            class="px-3 py-2 rounded border text-sm disabled:opacity-50"
            :disabled="loading || (pagination?.current_page || page) >= (pagination?.last_page || 1)"
            @click="nextPage"
          >
            Sau →
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center text-sm text-gray-600 py-6">Đang tải...</div>
      <XsTable v-else :xsmb="xsmb" title="XSMB" />
    </div>


    <!-- XSMN -->
    <div v-if="activeTab == 'xsmn'" class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-600">Chưa hỗ trợ.</div>
    </div>

    <!-- XSMT -->
    <div v-if="activeTab == 'xsmt'" class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-600">Chưa hỗ trợ.</div>
    </div>
  </div>
</template>
