<script setup>
const stats = ref([]);
const predictions = ref([]);
const api = useApi();
onMounted(async () => {
  const data = await api.getStats();
  stats.value = data.data;
  const predictionsData = await api.getPredictions();
  predictions.value = predictionsData.data;
});
</script>

<template>
  <div class="p-6 space-y-6">
    <PredictionCard :data="predictions" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <StatsTable :data="stats" />
      <ChartFrequency :data="stats" />
    </div>

    <NumberGrid :data="stats" />
  </div>
</template>
