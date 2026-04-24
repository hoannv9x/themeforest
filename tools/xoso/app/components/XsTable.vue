<template>
  <div>
    <div v-for="(data, index) in xsmb" :key="index" :class="{ 'my-20': !isToday(index) }">
      <div class="bg-white border rounded overflow-hidden">
        <!-- Header -->
        <div class="bg-red-600 text-white text-center py-2 font-bold uppercase max-sm:text-sm">
          🏆 {{ title }} - {{ isToday(index) ? 'Hôm nay' : index }}
        </div>
        <!-- Sub header -->
        <div class="bg-yellow-100 text-center py-2 text-sm">
          <span class="font-semibold" v-for="(n, imadb) in data.ma_db" :key="imadb">
            {{ n }} <span v-if="imadb < data.ma_db.length - 1"> - </span>
          </span>
        </div>

        <!-- Table -->
        <table class="w-full border-collapse text-center">
          <tbody>
            <!-- ĐB -->
            <tr class="border-t">
              <td class="w-32 bg-gray-100 font-semibold py-3 max-sm:text-xs max-sm:w-16">Đặc Biệt</td>
              <td class="py-3 max-sm:text-sm">
                <span class="text-red-600 text-4xl font-bold tracking-wider max-sm:text-2xl">
                  <span v-for="(n, idb) in data.db" :key="idb">{{ n }}</span>
                </span>
              </td>
            </tr>

            <!-- G1 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Nhất</td>
              <td class="py-3 font-semibold text-lg max-sm:text-md">
                <span v-for="(n, i1) in data.g1" :key="i1">{{ n }}</span>
              </td>
            </tr>

            <!-- G2 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Nhì</td>
              <td class="py-3 grid grid-cols-2 gap-2 max-sm:text-md">
                <span v-for="(n, i2) in data.g2" :key="i2">{{ n }}</span>
              </td>
            </tr>

            <!-- G3 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Ba</td>
              <td class="py-3 grid grid-cols-3 gap-y-2 max-sm:text-md">
                <span v-for="(n, i3) in data.g3" :key="i3">{{ n }}</span>
              </td>
            </tr>

            <!-- G4 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Tư</td>
              <td class="py-3 grid grid-cols-4 gap-2 max-sm:text-md">
                <span v-for="(n, i4) in data.g4" :key="i4">{{ n }}</span>
              </td>
            </tr>

            <!-- G5 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Năm</td>
              <td class="py-3 grid grid-cols-3 gap-y-2 max-sm:text-md">
                <span v-for="(n, i5) in data.g5" :key="i5">{{ n }}</span>
              </td>
            </tr>

            <!-- G6 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Sáu</td>
              <td class="py-3 grid grid-cols-3 gap-2 max-sm:text-md">
                <span v-for="(n, i6) in data.g6" :key="i6">{{ n }}</span>
              </td>
            </tr>

            <!-- G7 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Bảy</td>
              <td class="py-3 grid grid-cols-4 gap-2 text-red-500 font-semibold max-sm:text-md">
                <span v-for="(n, i7) in data.g7" :key="i7">{{ n }}</span>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="border-t bg-gray-50 p-3">
          <h4 class="font-semibold mb-2 text-sm">Bảng đầu - đuôi lô tô (2 số cuối)</h4>
          <table class="w-full border-collapse text-center text-sm">
            <thead>
              <tr>
                <th class="border px-2 py-1 bg-white w-20">Đầu</th>
                <th class="border px-2 py-1 bg-white">Đuôi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in getHeadTailRows(data)" :key="`head-${index}-${row.head}`">
                <td class="border px-2 py-1 font-semibold">{{ row.head }}</td>
                <td class="border px-2 py-1 text-left">{{ row.tails }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  xsmb: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    default: "Xổ số miền Bắc",
  },
});

const toYesterday = ref("");

const isToday = (index) => {
  return index == toYesterday.value;
};

onMounted(() => {
  const date = new Date();
  date.setDate(date.getDate() - 1);
  toYesterday.value = date.toISOString().slice(0, 10);
});

const prizeKeys = ["db", "g1", "g2", "g3", "g4", "g5", "g6", "g7"];

const normalizeToTwoDigits = (value) => {
  const digits = String(value ?? "").replace(/\D/g, "");
  if (!digits) return null;
  return digits.slice(-2).padStart(2, "0");
};

const getHeadTailRows = (result) => {
  const heads = Array.from({ length: 10 }, (_, i) => ({
    head: String(i),
    tailsList: [],
  }));

  prizeKeys.forEach((key) => {
    const values = Array.isArray(result?.[key]) ? result[key] : [];
    values.forEach((raw) => {
      const loto = normalizeToTwoDigits(raw);
      if (!loto) return;

      const head = Number(loto[0]);
      const tail = loto[1];
      heads[head].tailsList.push(tail);
    });
  });

  return heads.map((item) => ({
    head: item.head,
    tails: item.tailsList.sort((a, b) => Number(a) - Number(b)).join(", "),
  }));
};
</script>
