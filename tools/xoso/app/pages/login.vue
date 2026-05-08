<template>
  <div class="min-h-screen bg-gray-100 text-gray-900 flex justify-center">
    <div
      class="max-w-screen-xl m-0 sm:m-10 bg-white shadow sm:rounded-lg flex justify-center flex-1"
    >
      <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
        <div>
          <img
            src="/logo.png"
            class="w-64 mx-auto"
          />
        </div>
        <div class="mt-2 flex flex-col items-center">
          <h1 class="text-2xl xl:text-3xl font-extrabold">Sign In</h1>
          <div class="w-full flex-1 mt-8">
            <div class="flex flex-col items-center">
              <div ref="googleBtn" class="w-full max-w-xs" />
            </div>

            <div class="my-12 border-b text-center">
              <div
                class="leading-none px-2 inline-block text-sm text-gray-600 tracking-wide font-medium bg-white transform translate-y-1/2"
              >
                Or sign in with e-mail
              </div>
            </div>

            <form class="mx-auto max-w-xs" @submit.prevent="login">
              <input
                v-model="form.email"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                type="email"
                placeholder="Email"
                required
              />
              <input
                v-model="form.password"
                class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                type="password"
                placeholder="Password"
                required
              />
              <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
                <input v-model="rememberMe" type="checkbox" class="rounded" />
                <span>Ghi nhớ đăng nhập</span>
              </label>
              <div v-if="error" class="text-red-500 text-sm mt-4">{{ error }}</div>
              <button
                type="submit"
                class="mt-5 tracking-wide font-semibold bg-indigo-500 text-gray-100 w-full py-4 rounded-lg hover:bg-indigo-700 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none"
              >
                <svg
                  class="w-6 h-6 -ml-2"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                  <circle cx="8.5" cy="7" r="4" />
                  <path d="M20 8v6M23 11h-6" />
                </svg>
                <span class="ml-3"> Sign In </span>
              </button>
              <p class="mt-6 text-xs text-gray-600 text-center">
                Don't have an account?
                <NuxtLink to="/register" class="border-b border-gray-500 border-dotted">
                  Register
                </NuxtLink>
              </p>
            </form>
          </div>
        </div>
      </div>
      <div class="flex-1 bg-indigo-100 text-center hidden lg:flex">
        <div
          class="m-12 xl:m-16 w-full bg-contain bg-center bg-no-repeat"
          style="
            background-image: url('https://storage.googleapis.com/devitary-image-host.appspot.com/15848031292911696601-undraw_designer_life_w96d.svg');
          "
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "#app";
import { useAuthStore } from "~/stores/auth";

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Đăng nhập',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
  script: [
    { src: 'https://accounts.google.com/gsi/client', async: true, defer: true },
  ],
});

const authStore = useAuthStore();
const router = useRouter();
const config = useRuntimeConfig();

const googleBtn = ref(null);

const form = ref({
  email: "",
  password: "",
});
const error = ref(null);
const rememberMe = ref(false);

const rememberEmailKey = "remember_login_email";

const login = async () => {
  error.value = null;
  try {
    if (rememberMe.value) {
      localStorage.setItem(rememberEmailKey, form.value.email || "");
    } else {
      localStorage.removeItem(rememberEmailKey);
    }

    await authStore.login(form.value.email, form.value.password, rememberMe.value);
    router.push("/dashboard");
  } catch (err) {
    error.value = err.message || "Login failed. Please check your credentials.";
    console.error("Login error:", err);
  }
};

const initGoogle = async () => {
  if (!config.public.googleClientId) {
    console.log(123);
    
    return;
  }

  const waitForGoogle = () =>
    new Promise((resolve, reject) => {
      const started = Date.now();
      const timer = setInterval(() => {
        if (window.google?.accounts?.id) {
          clearInterval(timer);
          resolve(true);
          return;
        }
        if (Date.now() - started > 6000) {
          clearInterval(timer);
          reject(new Error('Google SDK not loaded'));
        }
      }, 100);
    });

  try {
    await waitForGoogle();
  } catch (e) {
    return;
  }

  if (!googleBtn.value) {
    return;
  }

  window.google.accounts.id.initialize({
    client_id: config.public.googleClientId,
    callback: async (response) => {
      error.value = null;
      try {
        if (rememberMe.value) {
          localStorage.setItem(rememberEmailKey, form.value.email || "");
        } else {
          localStorage.removeItem(rememberEmailKey);
        }

        await authStore.loginWithGoogle(response.credential, undefined, rememberMe.value);
        router.push('/dashboard');
      } catch (err) {
        error.value = err.message || 'Google login failed';
      }
    },
  });

  window.google.accounts.id.renderButton(googleBtn.value, {
    theme: 'outline',
    size: 'large',
    text: 'signin_with',
    shape: 'rectangular',
    width: 320,
  });
};

onMounted(() => {
  const savedEmail = localStorage.getItem(rememberEmailKey);
  if (savedEmail && !form.value.email) {
    form.value.email = savedEmail;
    rememberMe.value = true;
  }
  initGoogle();
});
</script>
