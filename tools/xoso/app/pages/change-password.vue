<script setup>
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Đổi mật khẩu',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();
const authStore = useAuthStore();

authStore.initAuth();
if (!authStore.getIsAuthenticated) {
  await navigateTo("/login");
}

const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const form = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const submit = async () => {
  errorMessage.value = "";
  successMessage.value = "";

  if (form.password !== form.password_confirmation) {
    errorMessage.value = "Mật khẩu mới không khớp.";
    return;
  }

  loading.value = true;
  try {
    const res = await api.changePassword({ ...form });
    successMessage.value = res.data?.message || "Đổi mật khẩu thành công.";
    form.current_password = "";
    form.password = "";
    form.password_confirmation = "";
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || "Đổi mật khẩu thất bại.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-xl mx-auto p-6 space-y-6">
    <div class="bg-white border rounded-xl p-6">
      <h1 class="text-2xl font-bold">Đổi mật khẩu</h1>
      <p class="text-gray-600 mt-2">Nhập mật khẩu hiện tại và mật khẩu mới.</p>
    </div>

    <div class="bg-white border rounded-xl p-6 space-y-4">
      <div class="space-y-1">
        <div class="text-sm font-semibold text-gray-700">Mật khẩu hiện tại</div>
        <input v-model="form.current_password" type="password" class="border rounded-lg px-3 py-2 w-full" />
      </div>

      <div class="space-y-1">
        <div class="text-sm font-semibold text-gray-700">Mật khẩu mới</div>
        <input v-model="form.password" type="password" class="border rounded-lg px-3 py-2 w-full" />
      </div>

      <div class="space-y-1">
        <div class="text-sm font-semibold text-gray-700">Xác nhận mật khẩu mới</div>
        <input v-model="form.password_confirmation" type="password" class="border rounded-lg px-3 py-2 w-full" />
      </div>

      <div v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</div>
      <div v-if="successMessage" class="text-sm text-green-700">{{ successMessage }}</div>

      <button
        class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
        :disabled="loading"
        @click="submit"
      >
        {{ loading ? "Đang lưu..." : "Đổi mật khẩu" }}
      </button>
    </div>
  </div>
</template>

