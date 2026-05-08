<script setup>
const api = useApi();
const referralInfo = ref(null);
const referralAttachCode = ref("");
const referralError = ref("");
const referralLoading = ref(false);

async function fetchReferralInfo() {
  try {
    const res = await api.getReferralMe();
    referralInfo.value = res.data || null;
  } catch (e) {
    referralInfo.value = null;
  }
}

async function attachReferral() {
  referralError.value = "";
  referralLoading.value = true;
  try {
    await api.attachReferral({ referral_code: referralAttachCode.value });
    referralAttachCode.value = "";
    await fetchReferralInfo();
  } catch (e) {
    referralError.value =
      e?.response?.data?.message || e?.message || "Không lưu được mã giới thiệu.";
  } finally {
    referralLoading.value = false;
  }
}

onMounted(async () => {
  await fetchReferralInfo();
});
</script>

<template>
  <div class="bg-white rounded-xl border p-6 max-sm:p-3">
    <h2 class="font-semibold mb-3">Giới thiệu bạn bè</h2>
    <div v-if="!referralInfo" class="text-sm text-gray-600">
      Chưa tải được thông tin giới thiệu.
    </div>
    <div v-else class="space-y-3 text-sm">
      <div>
        <div class="text-gray-500">Mã giới thiệu</div>
        <div class="flex items-center gap-2">
          <div class="flex-1 font-mono bg-gray-50 border rounded p-2 break-all">
            {{ referralInfo.referral_code }}
          </div>
          <CopyButton :text="referralInfo.referral_code" label="Copy" copied-label="Đã copy" />
        </div>
      </div>
      <div>
        <div class="text-gray-500">Link giới thiệu</div>
        <div class="flex items-center gap-2">
          <div class="flex-1 font-mono bg-gray-50 border rounded p-2 break-all">
            {{ referralInfo.referral_link }}
          </div>
          <CopyButton :text="referralInfo.referral_link" label="Copy" copied-label="Đã copy" />
        </div>
      </div>
      <div class="text-gray-600">
        Khi người được giới thiệu thanh toán thành công, bạn sẽ nhận coupon giảm
        <strong>5%</strong> dùng trong <strong>30 ngày</strong>.
      </div>

      <div v-if="!referralInfo.referred_by_user_id" class="pt-2 border-t">
        <div class="text-gray-500 mb-2">Bạn được giới thiệu bởi</div>
        <div class="flex gap-2">
          <input
            v-model="referralAttachCode"
            type="text"
            class="flex-1 border rounded-lg px-3 py-2 text-sm"
            placeholder="Nhập mã giới thiệu"
          />
          <button
            class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
            :disabled="referralLoading || !referralAttachCode"
            @click="attachReferral"
          >
            {{ referralLoading ? "Đang lưu..." : "Lưu" }}
          </button>
        </div>
        <div v-if="referralError" class="text-red-600 mt-2">{{ referralError }}</div>
      </div>
      <div v-else class="text-green-700 font-medium">
        Bạn đã nhập mã giới thiệu trước đó.
      </div>
    </div>
  </div>
</template>
