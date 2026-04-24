<script setup>
import { useAuthStore } from "~/stores/auth";

const api = useApi();
const authStore = useAuthStore();

const loading = ref(false);
const submitting = ref(false);
const payoutSubmitting = ref(false);
const err = ref("");
const successMsg = ref("");
const overview = ref(null);
const myPrediction = ref(null);
const selectedNumbers = ref([]);
const nowTs = ref(Date.now());
const payoutForm = ref({
  bank_name: "",
  bank_account_name: "",
  bank_account_number: "",
  note: "",
});
const payoutMsg = ref("");

const numberGrid = computed(() =>
  Array.from({ length: 100 }, (_, i) => String(i).padStart(2, "0"))
);

const leader = computed(() => overview.value?.leader || null);
const topNumbers = computed(() => overview.value?.top_numbers || []);
const predictedNumbers = computed(() => overview.value?.predicted_numbers || []);
const totalUsers = computed(() => overview.value?.total_users || 0);
const aiSuggestion = computed(() => overview.value?.ai_suggestion || {});
const countdownSeconds = computed(() => {
  if (!overview.value?.cutoff_at) return 0;
  const cutoff = new Date(overview.value.cutoff_at).getTime();
  return Math.max(0, Math.floor((cutoff - nowTs.value) / 1000));
});
const countdownLabel = computed(() => {
  const total = countdownSeconds.value;
  const h = String(Math.floor(total / 3600)).padStart(2, "0");
  const m = String(Math.floor((total % 3600) / 60)).padStart(2, "0");
  const s = String(total % 60).padStart(2, "0");
  return `${h}:${m}:${s}`;
});
const isCutoff = computed(() => countdownSeconds.value <= 0);

let timer = null;

const isSelected = (num) => selectedNumbers.value.includes(num);

const toggleNumber = (num) => {
  err.value = "";
  const idx = selectedNumbers.value.indexOf(num);
  if (idx >= 0) {
    selectedNumbers.value.splice(idx, 1);
    return;
  }

  if (selectedNumbers.value.length >= 5) {
    err.value = "Moi user chi duoc chon toi da 5 so.";
    return;
  }
  selectedNumbers.value.push(num);
};

const fetchOverview = async () => {
  loading.value = true;
  try {
    const { data } = await api.getMiniGameOverview();
    overview.value = data;
  } catch (e) {
    err.value = e?.response?.data?.message || "Khong tai duoc mini game.";
  } finally {
    loading.value = false;
  }
};

const fetchMyPrediction = async () => {
  if (!authStore.isAuthenticated) {
    myPrediction.value = null;
    return;
  }
  try {
    const { data } = await api.getMiniGameMe();
    myPrediction.value = data.prediction || null;
    selectedNumbers.value = data.prediction?.numbers || [];
  } catch (_e) {
    myPrediction.value = null;
  }
};

const submitPrediction = async () => {
  if (!authStore.isAuthenticated) {
    err.value = "Ban can dang nhap de tham gia mini game.";
    return;
  }
  if (selectedNumbers.value.length < 1) {
    err.value = "Vui long chon it nhat 1 so.";
    return;
  }

  err.value = "";
  successMsg.value = "";
  submitting.value = true;
  try {
    await api.submitMiniGamePrediction({ numbers: selectedNumbers.value });
    successMsg.value = "Da luu du doan thanh cong.";
    await Promise.all([fetchOverview(), fetchMyPrediction()]);
  } catch (e) {
    err.value = e?.response?.data?.message || "Luu du doan that bai.";
  } finally {
    submitting.value = false;
  }
};

const submitPayoutRequest = async () => {
  payoutMsg.value = "";
  payoutSubmitting.value = true;
  try {
    await api.submitMiniGamePayoutRequest(payoutForm.value);
    payoutMsg.value = "Da gui thong tin STK thanh cong.";
  } catch (e) {
    payoutMsg.value = e?.response?.data?.message || "Gui thong tin STK that bai.";
  } finally {
    payoutSubmitting.value = false;
  }
};

onMounted(async () => {
  await Promise.all([fetchOverview(), fetchMyPrediction()]);
  timer = setInterval(() => {
    nowTs.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<template>
  <div class="bg-white border rounded-xl p-6 space-y-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold">Mini game du doan nguoi dung</h2>
        <p class="text-sm text-gray-600">Chot du doan luc 17:00 hang ngay.</p>
      </div>
      <div class="text-sm font-semibold px-3 py-2 rounded-lg border bg-gray-50">
        Countdown: {{ countdownLabel }}
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <p class="text-sm text-gray-600">So nguoi da du doan</p>
        <p class="text-3xl font-bold">{{ totalUsers }}</p>
      </div>
      <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
        <p class="text-sm text-gray-600">Goi y AI noi bo</p>
        <p class="font-semibold">{{ aiSuggestion?.numbers?.join(", ") || "--" }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ aiSuggestion?.message }}</p>
      </div>
    </div>

    <div v-if="leader" class="border rounded-xl p-4 text-center bg-amber-50 border-amber-300">
      <p class="text-sm text-gray-600 mb-2">So duoc vote cao nhat hom nay</p>
      <div class="text-5xl font-extrabold text-red-600 leading-none">{{ leader.number }}</div>
      <p class="mt-2 text-sm">Luot binh chon: <strong>{{ leader.votes }}</strong></p>
    </div>

    <div>
      <h3 class="font-semibold mb-2">Top 10 so duoc binh chon</h3>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="(item, idx) in topNumbers"
          :key="`top-${idx}-${item.number}`"
          class="px-3 py-2 rounded-lg bg-gray-100 text-sm font-semibold"
        >
          {{ item.number }} ({{ item.votes }})
        </div>
      </div>
    </div>

    <div>
      <h3 class="font-semibold mb-2">Tat ca so da du doan</h3>
      <div class="flex flex-wrap gap-2 max-h-40 overflow-auto">
        <div
          v-for="(item, idx) in predictedNumbers"
          :key="`all-${idx}-${item.number}`"
          class="px-2 py-1 rounded border text-xs"
        >
          {{ item.number }}: {{ item.votes }}
        </div>
      </div>
    </div>

    <div v-if="!authStore.isAuthenticated" class="border rounded-lg p-3 text-sm bg-yellow-50 border-yellow-200">
      Ban can tai khoan de tham gia mini game.
      <NuxtLink to="/register" class="font-semibold underline ml-1">Dang ky ngay</NuxtLink>
      hoac
      <NuxtLink to="/login" class="font-semibold underline ml-1">Dang nhap</NuxtLink>.
    </div>

    <div v-else class="space-y-3">
      <h3 class="font-semibold">Chon toi da 5 so du doan</h3>
      <div class="grid grid-cols-10 gap-2 max-h-56 overflow-auto">
        <button
          v-for="num in numberGrid"
          :key="`pick-${num}`"
          class="text-xs rounded px-2 py-2 border font-semibold"
          :class="isSelected(num) ? 'bg-red-500 text-white border-red-500' : 'bg-white hover:bg-gray-100'"
          @click="toggleNumber(num)"
        >
          {{ num }}
        </button>
      </div>

      <p class="text-sm">Da chon: <strong>{{ selectedNumbers.join(", ") || "--" }}</strong></p>
      <p v-if="myPrediction" class="text-xs text-gray-500">
        Du doan cua ban da luu luc: {{ myPrediction.updated_at || myPrediction.created_at }}
      </p>

      <div class="flex gap-2">
        <button
          class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-50"
          :disabled="submitting || isCutoff"
          @click="submitPrediction"
        >
          {{ submitting ? "Dang luu..." : isCutoff ? "Da het gio du doan" : "Luu du doan" }}
        </button>
        <button class="border px-4 py-2 rounded-lg text-sm" @click="fetchOverview">
          Refresh
        </button>
      </div>
    </div>

    <div class="space-y-3 border-t pt-4" v-if="authStore.isAuthenticated">
      <h3 class="font-semibold">Form nhan thuong (nguoi thang tuan)</h3>
      <div class="grid md:grid-cols-2 gap-3">
        <input v-model="payoutForm.bank_name" class="border rounded-lg px-3 py-2 text-sm" placeholder="Ten ngan hang" />
        <input v-model="payoutForm.bank_account_name" class="border rounded-lg px-3 py-2 text-sm" placeholder="Chu tai khoan" />
        <input v-model="payoutForm.bank_account_number" class="border rounded-lg px-3 py-2 text-sm md:col-span-2" placeholder="So tai khoan" />
      </div>
      <textarea
        v-model="payoutForm.note"
        class="border rounded-lg px-3 py-2 text-sm w-full"
        rows="2"
        placeholder="Ghi chu (khong bat buoc)"
      />
      <button
        class="bg-black text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50"
        :disabled="payoutSubmitting"
        @click="submitPayoutRequest"
      >
        {{ payoutSubmitting ? "Dang gui..." : "Gui thong tin STK" }}
      </button>
    </div>

    <p v-if="err" class="text-sm text-red-600">{{ err }}</p>
    <p v-if="successMsg" class="text-sm text-green-600">{{ successMsg }}</p>
    <p v-if="payoutMsg" class="text-sm text-blue-600">{{ payoutMsg }}</p>
    <div v-if="loading" class="text-sm text-gray-500">Dang tai du lieu mini game...</div>
  </div>
</template>
