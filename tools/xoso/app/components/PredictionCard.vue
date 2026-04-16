<template>
  <div class="bg-gradient-to-r from-red-500 to-orange-500 p-6 rounded-xl text-white">
    <h2 class="text-xl font-bold mb-4">🎯 Gợi ý hôm nay</h2>
    <!-- check data -->
    <div v-if="!data || !data.top_numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div v-else class="flex gap-3 flex-wrap">
      <div
        v-for="(item, index) in data?.top_numbers || []"
        :key="item?.key"
        class="bg-white text-black px-4 py-3 rounded-lg font-bold text-center"
      >
        <div class="text-lg">{{ index }}</div>
      </div>
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
