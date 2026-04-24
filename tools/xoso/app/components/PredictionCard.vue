<template>
  <div
    class="bg-gradient-to-r from-red-500 via-red-400 to-orange-400 p-6 rounded-xl text-white"
  >
    <h2 class="text-xl font-bold mb-4">Gợi ý lô hôm nay</h2>
    <!-- check data -->
    <div v-if="!data || !data.predictions?.ranking?.numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div
      v-else
      class="grid gap-3"
      :class="{
        'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5':
          data.predictions?.ranking?.numbers[0].label,
        'grid-cols-3 md:grid-cols-5': !data.predictions?.ranking?.numbers[0].label,
      }"
    >
      <div
        v-for="(item, index) in data.predictions?.ranking?.numbers || []"
        :key="index"
        class="bg-white text-black px-4 py-3 rounded-lg font-bold text-center flex justify-center items-center flex-col"
        :class="{ 'max-sm:col-span-2': index == 2 && data.predictions?.ranking?.numbers[0].label }"
      >
        <div class="text-2xl font-extrabold tracking-wide">
          {{ item.number }}
        </div>

        <div
          v-if="item.label"
          class="text-xs mt-1 text-gray-500 flex items-center justify-center gap-1"
        >
          {{ item.label }}
        </div>

        <div v-if="item.message" class="text-xs mt-2 text-gray-600 leading-snug">
          {{ item.message }}
        </div>
      </div>
    </div>
    <hr class="my-4" />
    <h3 class="text-xl font-bold mb-4">Gợi ý đề hôm nay</h3>
    <div v-if="!data || !data.predictions?.db_ranking?.numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div
      v-else
      class="grid gap-3"
      :class="{
        'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5':
          data.predictions?.db_ranking?.numbers[0].label,
        'grid-cols-3 md:grid-cols-5': !data.predictions?.db_ranking?.numbers[0].label,
      }"
    >
      <div
        v-for="(item, index) in data?.predictions?.db_ranking?.numbers || []"
        :key="index"
        class="bg-white text-black px-4 py-3 rounded-lg font-bold text-center flex justify-center items-center flex-col"
        :class="{ 'max-sm:col-span-2': index == 2 && data.predictions?.db_ranking?.numbers[0].label }"
      >
        <div class="text-2xl font-extrabold tracking-wide">
          {{ item.number }}
        </div>

        <div
          v-if="item.label"
          class="text-xs mt-1 text-gray-500 flex items-center justify-center gap-1"
        >
          {{ item.label }}
        </div>

        <div v-if="item.message" class="text-xs mt-2 text-gray-600 leading-snug">
          {{ item.message }}
        </div>
      </div>
    </div>

    <div
      v-if="data?.predictions?.xien_2?.numbers?.length || data?.predictions?.xien_3?.numbers?.length || data?.predictions?.xien_4?.numbers?.length"
      class="mt-6 space-y-4"
    >
      <h3 class="text-xl font-bold mb-2">Gợi ý xiên VIP</h3>
      <div v-if="data?.predictions?.xien_2?.numbers?.length" class="space-y-2">
        <p class="font-semibold">Xiên 2</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="(item, index) in data.predictions.xien_2.numbers"
            :key="`x2-${index}`"
            class="bg-white text-black px-3 py-2 rounded-lg text-sm font-bold"
          >
            {{ item.numbers?.join(" - ") }}
          </span>
        </div>
      </div>

      <div v-if="data?.predictions?.xien_3?.numbers?.length" class="space-y-2">
        <p class="font-semibold">Xiên 3</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="(item, index) in data.predictions.xien_3.numbers"
            :key="`x3-${index}`"
            class="bg-white text-black px-3 py-2 rounded-lg text-sm font-bold"
          >
            {{ item.numbers?.join(" - ") }}
          </span>
        </div>
      </div>

      <div v-if="data?.predictions?.xien_4?.numbers?.length" class="space-y-2">
        <p class="font-semibold">Xiên 4</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="(item, index) in data.predictions.xien_4.numbers"
            :key="`x4-${index}`"
            class="bg-white text-black px-3 py-2 rounded-lg text-sm font-bold"
          >
            {{ item.numbers?.join(" - ") }}
          </span>
        </div>
      </div>
    </div>

    <div v-if="data?.predictions?.three_cang?.numbers?.length" class="mt-6 space-y-3">
      <h3 class="text-xl font-bold">Gợi ý 3 càng (3 số cuối giải đặc biệt)</h3>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(item, index) in data.predictions.three_cang.numbers"
          :key="`3c-${index}`"
          class="bg-white text-black px-3 py-2 rounded-lg text-sm font-bold"
        >
          {{ item.number }}
        </span>
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
