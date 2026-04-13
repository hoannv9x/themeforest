<template>
  <div class="bg-gradient-to-r from-red-500 to-orange-500 p-6 rounded-xl text-white">
    <h2 class="text-xl font-bold mb-4">🎯 Gợi ý hôm nay</h2>
    <!-- check data -->
    <div v-if="!data || !data.numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div v-else class="flex gap-3 flex-wrap">
      <div
        v-for="item in data?.numbers || []"
        :key="item?.numbers"
        class="bg-white text-black px-4 py-3 rounded-lg font-bold text-center"
      >
        <div class="text-lg">{{ item.number }}</div>
        <div class="text-xs opacity-70">{{ item.score }}%</div>
      </div>
    </div>

    <!-- meta -->
    <div v-if="data?.meta" class="mt-4 text-sm opacity-80">
      Độ tin cậy: {{ data.meta.confidence_avg }}%
    </div>

    <p class="mt-3">Lưu ý</p>
    <p class="text-xs opacity-70 mt-1">Dữ liệu được phân tích từ lịch sử gần đây</p>
    <p class="text-xs opacity-70">
      Điểm score thể hiện mức độ nổi bật của số dựa trên dữ liệu thống kê, không phải xác
      suất trúng.
    </p>
    <p class="text-xs opacity-70">Chỉ mang tính tham khảo</p>
  </div>
</template>

<script setup>
defineProps(["data"]);
</script>
