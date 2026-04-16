<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";
import { Line } from "vue-chartjs";
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);
const emit = defineEmits(["selectedDate"]);
const selectedDate = ref("0");
const props = defineProps(["data"]);

const chartData = computed(() => ({
  labels: props.data.map((i) => i.number),
  datasets: [
    {
      label: "Tần suất",
      backgroundColor: "#f87979",
      data: props.data.map((i) => i.score),
    },
  ],
}));

watch(selectedDate, (newVal) => {
  if (newVal) {
    emit("selectedDate", newVal);
  }
});
</script>

<template>
  <div class="bg-white p-4 rounded-xl">
    <!-- Date picker -->
    <div class="mb-4">
      <label for="date" class="block mb-2.5 text-sm font-medium text-heading"
        >Chọn ngày bắt đầu</label
      >
      <select
        id="date"
        v-model="selectedDate"
        class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
      >
        <option selected value="0">Thống kê từ ngày 01-01-2025</option>
        <option value="7">7 ngày trước</option>
        <option value="30">30 ngày trước</option>
        <option value="90">90 ngày trước</option>
        <option value="180">180 ngày trước</option>
        <option value="365">1 năm trước</option>
      </select>
    </div>

    <!-- Frequency table -->
    <div class="mb-4">
      <h3 class="text-lg font-semibold mb-2">Bảng tần suất</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider text-center"
              >
                Số
              </th>
              <th
                class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider text-center"
              >
                Tần suất
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in props.data" :key="item.number">
              <td class="px-4 py-2 text-sm text-gray-900 text-center">{{ item.number }}</td>
              <td class="px-4 py-2 text-sm text-gray-900 text-center">{{ item.score }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="max-sm:hidden">
      <Line :data="chartData" />
    </div>
  </div>
</template>
