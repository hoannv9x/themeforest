<template>
  <div class="min-h-screen bg-gray-100 text-gray-900 flex justify-center">
    <div
      class="max-w-screen-xl m-0 sm:m-10 bg-white shadow sm:rounded-lg flex justify-center flex-1"
    >
      <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
        <div>
          <div class="w-24 h-24 bg-red-500 rounded-xl mx-auto flex items-center justify-center">
            <span class="text-white text-4xl font-bold">XO</span>
          </div>
        </div>
        <div class="mt-8 flex flex-col items-center">
          <h1 class="text-2xl xl:text-3xl font-extrabold">Đăng ký</h1>
          <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 w-full max-w-xs">
            <p class="text-yellow-700 text-sm text-center">
              🎁 Đăng ký ngay để nhận <strong>3 ngày VIP MIỄN PHÍ</strong>!
            </p>
          </div>
          <div class="w-full flex-1 mt-8">
            <div class="my-8 border-b text-center">
              <div
                class="leading-none px-2 inline-block text-sm text-gray-600 tracking-wide font-medium bg-white transform translate-y-1/2"
              >
                Đăng ký với email
              </div>
            </div>

            <form class="mx-auto max-w-xs" @submit.prevent="register">
              <input
                v-model="form.name"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                type="text"
                placeholder="Họ tên"
                required
              />
              <input
                v-model="form.email"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                type="email"
                placeholder="Email"
                required
              />
              <input
                v-model="form.password"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                type="password"
                placeholder="Mật khẩu"
                required
              />
              <input
                v-model="form.confirmPassword"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                type="password"
                placeholder="Xác nhận mật khẩu"
                required
              />
              <div v-if="error" class="text-red-500 text-sm mt-4">{{ error }}</div>
              <button
                type="submit"
                class="mt-5 tracking-wide font-semibold bg-red-500 text-white w-full py-4 rounded-lg hover:bg-red-600 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none"
              >
                <span class="ml-3"> Đăng ký (3 ngày VIP miễn phí) </span>
              </button>
              <p class="mt-6 text-xs text-gray-600 text-center">
                Đã có tài khoản?
                <NuxtLink to="/login" class="border-b border-gray-500 border-dotted">
                  Đăng nhập
                </NuxtLink>
              </p>
            </form>
          </div>
        </div>
      </div>
      <div class="flex-1 bg-red-50 text-center hidden lg:flex flex-col justify-center p-12">
        <h2 class="text-3xl font-bold text-red-600 mb-6">Trải nghiệm VIP MIỄN PHÍ!</h2>
        <div class="text-left space-y-4 max-w-md mx-auto">
          <div class="flex items-start gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <div>
              <p class="font-semibold">Full bộ số + phân tích</p>
              <p class="text-gray-600 text-sm">Xem tất cả dự đoán chi tiết</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <div>
              <p class="font-semibold">Heatmap số</p>
              <p class="text-gray-600 text-sm">Thống kê xác suất chi tiết</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <div>
              <p class="font-semibold">Chu kỳ lặp</p>
              <p class="text-gray-600 text-sm">Phân tích lịch sử trúng</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "#app";
import { useAuthStore } from "~/stores/auth";

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
  name: "",
  email: "",
  password: "",
  confirmPassword: "",
});
const error = ref(null);

const register = async () => {
  error.value = null;
  if (form.value.password !== form.value.confirmPassword) {
    error.value = "Mật khẩu không khớp.";
    return;
  }
  try {
    await authStore.register(form.value.name, form.value.email, form.value.password, form.value.confirmPassword);
    router.push("/dashboard");
  } catch (err) {
    error.value = err.message || "Đăng ký thất bại. Vui lòng thử lại.";
    console.error("Registration error:", err);
  }
};

useHead({
  title: "Đăng ký - XoSo AI",
  meta: [{ name: "description", content: "Đăng ký tài khoản và nhận 3 ngày VIP miễn phí!" }],
});
</script>
