<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
      <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Register</h1>
      <form @submit.prevent="register">
        <div class="mb-4">
          <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
          <input type="text" id="name" v-model="form.name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
          <input type="email" id="email" v-model="form.email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
          <input type="password" id="password" v-model="form.password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
          <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
          <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Register
          </button>
          <NuxtLink to="/login" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
            Already have an account? Login
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const error = ref(null);

const register = async () => {
  error.value = null;
  try {
    await authStore.register(form.value.name, form.value.email, form.value.password, form.value.password_confirmation);
    router.push('/dashboard'); // Redirect to dashboard on successful registration
  } catch (err) {
    error.value = err.message || 'Registration failed. Please try again.';
    console.error('Registration error:', err);
  }
};

useHead({
  title: 'Register - Real Estate',
  meta: [
    { name: 'description', content: 'Create a new account on the real estate platform.' },
  ],
});
</script>