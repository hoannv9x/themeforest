<template>
  <div class="max-w-6xl mx-auto p-4">
    <!-- Title -->
    <h1 class="text-2xl font-bold text-center mb-6">Kết quả xổ số hôm nay</h1>

    <!-- Tabs -->
    <div class="flex justify-center mb-6 space-x-2">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        :class="[
          'px-4 py-2 rounded border',
          activeTab === tab.key ? 'bg-red-500 text-white' : 'bg-white',
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- XSMB -->
    <div v-if="activeTab == 'xsmb'" class="bg-white rounded shadow p-4">
      <XsTable :xsmb="xsmb" title="Xổ số Miền Bắc" />
    </div>

    <!-- XSMN -->
    <div v-if="activeTab == 'xsmn'" class="bg-white rounded shadow p-4">
      <XsTable :xsmb="xsmn" title="Xổ số Miền Nam" />
    </div>

    <!-- XSMT -->
    <div v-if="activeTab == 'xsmt'" class="bg-white rounded shadow p-4">
      <XsTable :xsmb="xsmt" title="Xổ số Miền Trung" />
    </div>
  </div>
</template>

<script setup>
const activeTab = ref("xsmb");
const api = useApi();
const tabs = [
  { key: "xsmb", label: "XSMB" },
  { key: "xsmn", label: "XSMN" },
  { key: "xsmt", label: "XSMT" },
];

/**
 * Mock data (sau này replace bằng API)
 */
const xsmb = ref([]);

const xsmn = ref({
  provinces: ["TP.HCM", "Cần Thơ", "Đồng Nai"],
  db: ["123456", "654321", "999999"],
  g1: ["11111", "22222", "33333"],
});

const xsmt = ref({
  provinces: ["Đà Nẵng", "Huế"],
  db: ["888888", "777777"],
  g1: ["12345", "54321"],
});

async function getResult() {
  try {
    const { data } = await api.getResults({
      is_multi_region: true,
    });

    xsmb.value = data.mb;
    xsmn.value = data.mn;
    xsmt.value = data.mt;
  } catch (e) {
    console.log(e);
  }
}

onMounted(() => {
  getResult();
});
</script>
