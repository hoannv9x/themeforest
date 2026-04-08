<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
      <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Login</h1>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
          <input type="email" id="email" v-model="form.email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
          <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
          <input type="password" id="password" v-model="form.password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Sign In
          </button>
          <NuxtLink to="/register" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
            Don't have an account? Register
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from '#app';
import { useAuthStore } from '~/stores/auth'; // Will be created in Step 8

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
  email: '',
  password: '',
});
const error = ref(null);

const login = async () => {
  error.value = null;
  try {
    await authStore.login(form.value.email, form.value.password);
    router.push('/dashboard'); // Redirect to dashboard on successful login
  } catch (err) {
    error.value = err.message || 'Login failed. Please check your credentials.';
    console.error('Login error:', err);
  }
};

useHead({
  title: 'Login - Real Estate',
  meta: [
    { name: 'description', content: 'Login to your real estate account.' },
  ],
});
</script>