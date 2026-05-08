<script setup>
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "apply"]);

const api = useApi();
const { formatDate } = useFormatters();

const loading = ref(false);
const errorMessage = ref("");
const coupons = ref([]);

const fetchCoupons = async () => {
  loading.value = true;
  errorMessage.value = "";
  try {
    const res = await api.getMyCoupons();
    coupons.value = res.data || [];
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || "Không tải được coupon.";
    coupons.value = [];
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.open,
  async (v) => {
    if (v) {
      await fetchCoupons();
    }
  },
  { immediate: true }
);

const close = () => emit("close");
const apply = (code) => emit("apply", code);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
    <button class="absolute inset-0 bg-black/40" type="button" @click="close" />
    <div class="relative w-full max-w-xl bg-white rounded-xl shadow border p-5 mx-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold">Chọn coupon</h3>
        <button type="button" class="text-gray-500 hover:text-gray-800" @click="close">✕</button>
      </div>

      <div v-if="errorMessage" class="text-sm text-red-600 mt-3">{{ errorMessage }}</div>

      <div v-if="loading" class="text-sm text-gray-600 mt-3">Đang tải...</div>
      <div v-else class="mt-3">
        <div v-if="!coupons.length" class="text-sm text-gray-600">Bạn chưa có coupon nào còn hiệu lực.</div>
        <div v-else class="space-y-2 max-h-[60vh] overflow-auto">
          <div v-for="c in coupons" :key="c.id" class="border rounded-lg p-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <div class="font-mono font-semibold truncate">{{ c.code }}</div>
                <CopyButton :text="c.code" label="Copy" copied-label="Đã copy" />
              </div>
              <div class="text-xs text-gray-600 mt-1">
                Giảm <span class="font-semibold">{{ c.discount_percent }}%</span>
                <span v-if="c.expires_at"> · Hết hạn {{ formatDate(c.expires_at, true, 'DD-MM-YYYY') }}</span>
              </div>
            </div>
            <button
              type="button"
              class="shrink-0 bg-blue-600 text-white rounded-lg px-3 py-2 text-sm font-semibold hover:bg-blue-500"
              @click="apply(c.code)"
            >
              Apply
            </button>
          </div>
        </div>
      </div>

      <div class="mt-4 flex justify-end">
        <button type="button" class="border rounded-lg px-4 py-2 text-sm font-semibold" @click="close">Đóng</button>
      </div>
    </div>
  </div>
</template>

