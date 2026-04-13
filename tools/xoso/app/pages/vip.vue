<script setup>
const user = useState("user");

const { data, error } = await useFetch("/api/v1/vip/predictions");
</script>

<template>
  <div class="p-6">
    <div v-if="error">
      <h2 class="text-xl font-bold">🔒 Nội dung VIP</h2>
      <button class="bg-yellow-500 px-4 py-2 mt-3 rounded">Nâng cấp VIP</button>
    </div>

    <div v-else>
      <PredictionCard :data="data" />
    </div>
  </div>
</template>
