<script setup>
const stats = ref([]);
const predictions = ref([]);
const numbersMost = ref([]);
const api = useApi();
const paramNumberMost = ref({
  region: 'mb',
});

onMounted(async () => {
  const data = await api.getStats();
  stats.value = data.data.filter((i) => i.region == 'mb');
  
  const numberData = await api.getMostFrequentNumbers(paramNumberMost.value);
  numbersMost.value = numberData.data;
  
  const predictionsData = await api.getPredictions();
  predictions.value = predictionsData.data;
});

const handleSelectedDate = async (day) => {
  paramNumberMost.value.day = day;
  const numberData = await api.getMostFrequentNumbers(paramNumberMost.value);
  numbersMost.value = numberData.data;
}
</script>

<template>
  <div class="p-6 space-y-6">
    <PredictionCard :data="predictions" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <StatsTable :data="stats" />
      <ChartFrequency :data="numbersMost" @selectedDate="handleSelectedDate" />
    </div>

    <NumberGrid :data="stats" />
  </div>
</template>
