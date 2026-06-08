<template>
  <div>
    <div
      v-for="(row, idx) in normalizedRows"
      :key="row.date"
      :class="{ 'my-8 md:my-20': !isToday(row.date) && idx > 0 }"
    >
      <div class="bg-white border rounded overflow-hidden">
        <!-- Header -->
        <div
          class="bg-red-600 text-white text-center py-2 font-bold uppercase max-sm:text-sm"
        >
          🏆 {{ title }} - {{ isToday(row.date) ? "Hôm nay" : formatDmy(row.date) }}
        </div>
        <!-- Sub header -->
        <div class="bg-yellow-100 text-center py-2 text-sm">
          <span
            class="font-semibold"
            v-for="(n, imadb) in row.raw_data.ma_db"
            :key="imadb"
          >
            {{ n }} <span v-if="imadb < row.raw_data.ma_db.length - 1"> - </span>
          </span>
        </div>

        <!-- Table -->
        <table class="w-full border-collapse text-center">
          <tbody>
            <!-- ĐB -->
            <tr class="border-t">
              <td class="w-32 bg-gray-100 font-semibold py-3 max-sm:text-xs max-sm:w-16">
                Đặc Biệt
              </td>
              <td class="py-3 max-sm:text-sm">
                <span
                  class="text-red-600 text-4xl font-bold tracking-wider max-sm:text-2xl"
                >
                  <span
                    v-for="(n, idb) in row.raw_data.db"
                    :key="idb"
                    class="relative"
                    :class="
                      isHit(n, row, 'db') || isVipHit(n, row, 'db')
                        ? 'bg-green-500 text-white px-2 py-1 rounded'
                        : ''
                    "
                  >
                    {{ typeof n === "object" ? n.number : n }}
                    <span
                      v-if="isVipHit(n, row, 'db')"
                      class="absolute -top-3 -right-3 text-yellow-400 text-sm"
                      >⭐</span
                    >
                  </span>
                </span>
              </td>
            </tr>

            <!-- G1 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Nhất</td>
              <td
                class="py-3 font-semibold text-lg max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i1) in row.raw_data.g1"
                  :key="i1"
                  class="relative"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-2 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G2 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Nhì</td>
              <td
                class="py-3 grid grid-cols-2 gap-2 max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i2) in row.raw_data.g2"
                  :key="i2"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G3 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Ba</td>
              <td
                class="py-3 grid grid-cols-3 gap-2 max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i3) in row.raw_data.g3"
                  :key="i3"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G4 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Tư</td>
              <td
                class="py-3 grid grid-cols-4 gap-2 max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i4) in row.raw_data.g4"
                  :key="i4"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G5 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Năm</td>
              <td
                class="py-3 grid grid-cols-3 gap-2 max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i5) in row.raw_data.g5"
                  :key="i5"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G6 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Sáu</td>
              <td
                class="py-3 grid grid-cols-3 gap-2 max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i6) in row.raw_data.g6"
                  :key="i6"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
              </td>
            </tr>

            <!-- G7 -->
            <tr class="border-t">
              <td class="bg-gray-100 py-3 max-sm:text-xs">Giải Bảy</td>
              <td
                class="py-3 grid grid-cols-4 gap-2 text-red-500 font-semibold max-sm:text-md items-center justify-center"
              >
                <span
                  v-for="(n, i7) in row.raw_data.g7"
                  :key="i7"
                  class="relative inline-block"
                  :class="
                    isHit(n, row, 'loto') || isVipHit(n, row, 'loto')
                      ? 'bg-green-500 text-white px-2 py-1 rounded'
                      : ''
                  "
                >
                  {{ typeof n === "object" ? n.number : n }}
                  <span
                    v-if="isVipHit(n, row, 'loto')"
                    class="absolute -top-2 -right-1 text-yellow-400 text-xs"
                    >⭐</span
                  >
                </span>
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
              <tr
                v-for="ht in getHeadTailRows(row.raw_data)"
                :key="`head-${row.date}-${ht.head}`"
              >
                <td class="border px-2 py-1 font-semibold">{{ ht.head }}</td>
                <td class="border px-2 py-1 text-left">{{ ht.tails }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  xsmb: {
    type: [Array, Object],
    required: true,
  },
  title: {
    type: String,
    default: "Xổ số miền Bắc",
  },
});

const todayYmd = ref("");

const toLocalYmd = (d) => {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const isToday = (ymd) => {
  return String(ymd || "") === todayYmd.value;
};

onMounted(() => {
  todayYmd.value = toLocalYmd(new Date());
});

const prizeKeys = ["db", "g1", "g2", "g3", "g4", "g5", "g6", "g7"];

const normalizeToTwoDigits = (value) => {
  const digits = String(value ?? "").replace(/\D/g, "");
  if (!digits) return null;
  return digits.slice(-2).padStart(2, "0");
};

const formatDmy = (ymd) => {
  const s = String(ymd || "");
  if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
  const [y, m, d] = s.split("-");
  return `${d}-${m}-${y}`;
};

const normalizedRows = computed(() => {
  const normalizeRawData = (raw) => {
    const src = raw && typeof raw === "object" ? raw : {};
    return {
      ma_db: Array.isArray(src.ma_db) ? src.ma_db : [],
      db: Array.isArray(src.db) ? src.db : [],
      g1: Array.isArray(src.g1) ? src.g1 : [],
      g2: Array.isArray(src.g2) ? src.g2 : [],
      g3: Array.isArray(src.g3) ? src.g3 : [],
      g4: Array.isArray(src.g4) ? src.g4 : [],
      g5: Array.isArray(src.g5) ? src.g5 : [],
      g6: Array.isArray(src.g6) ? src.g6 : [],
      g7: Array.isArray(src.g7) ? src.g7 : [],
    };
  };

  const src = props.xsmb;
  if (Array.isArray(src)) {
    return src
      .map((item) => {
        const date = String(item?.date || "").slice(0, 10);
        const raw_data = normalizeRawData(item?.raw_data);
        return {
          date,
          raw_data,
          prediction_hits: item?.prediction_hits || null,
        };
      })
      .filter((r) => r.date && r.raw_data);
  }

  if (src && typeof src === "object") {
    return Object.entries(src)
      .map(([date, raw_data]) => ({
        date,
        raw_data: normalizeRawData(raw_data),
        prediction_hits: null,
      }))
      .sort((a, b) => String(b.date).localeCompare(String(a.date)));
  }

  return [];
});

const isHit = (rawValue, row, kind) => {
  const val = typeof rawValue === "object" ? rawValue.number : rawValue;
  const loto = normalizeToTwoDigits(val);
  if (!loto) return false;

  const hits =
    kind === "db" ? row?.prediction_hits?.db_numbers : row?.prediction_hits?.loto_numbers;

  return Array.isArray(hits) && hits.includes(loto);
};

const isVipHit = (rawValue, row, kind) => {
  const val = typeof rawValue === "object" ? rawValue.number : rawValue;
  const loto = normalizeToTwoDigits(val);
  if (!loto) return false;

  const hits =
    kind === "db"
      ? row?.prediction_hits?.vip_db_numbers
      : row?.prediction_hits?.vip_loto_numbers;

  return Array.isArray(hits) && hits.includes(loto);
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
