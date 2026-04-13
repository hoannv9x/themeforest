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

const props = defineProps(["data"]);

const chartData = computed(() => ({
  labels: props.data.slice(0, 10).map((i) => i.number),
  datasets: [
    {
      label: "Tần suất",
      backgroundColor: "#f87979",
      data: props.data.slice(0, 10).map((i) => i.total_count),
    },
  ],
}));
</script>

<template>
  <div class="bg-white p-4 rounded-xl">
    <Line :data="chartData" />
  </div>
</template>
