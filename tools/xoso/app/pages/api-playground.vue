<script setup>
const api = useApi();
const resultDate = ref("");
const resultData = ref(null);
const statusData = ref(null);

const fetchResults = async () => {
  const response = await api.getResults(resultDate.value ? { date: resultDate.value } : {});
  resultData.value = response.data;
};

const fetchStats = async () => {
  const response = await api.getStats();
  statusData.value = response.data;
};
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <section class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">API Playground</h1>
      <p class="text-gray-600 mt-2">
        Thu nghiem nhanh cac API duoc cung cap: ket qua xs, thong ke, du doan.
      </p>
    </section>

    <section class="bg-white border rounded-xl p-6 space-y-3">
      <h2 class="font-semibold">Test API ket qua</h2>
      <div class="flex gap-3">
        <input v-model="resultDate" type="date" class="border rounded px-3 py-2" />
        <button class="bg-blue-600 text-white px-4 py-2 rounded" @click="fetchResults">
          Goi /v1/results
        </button>
      </div>
      <pre class="bg-gray-900 text-green-300 p-4 rounded overflow-auto text-xs">{{ JSON.stringify(resultData, null, 2) }}</pre>
    </section>

    <section class="bg-white border rounded-xl p-6 space-y-3">
      <h2 class="font-semibold">Test API thong ke</h2>
      <button class="bg-purple-600 text-white px-4 py-2 rounded" @click="fetchStats">
        Goi /v1/stats
      </button>
      <pre class="bg-gray-900 text-green-300 p-4 rounded overflow-auto text-xs">{{ JSON.stringify(statusData, null, 2) }}</pre>
    </section>
  </div>
</template>
