<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-8">My Dashboard</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Navigation Sidebar -->
      <aside class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
        <nav>
          <ul>
            <li class="mb-4">
              <NuxtLink to="/dashboard" exact-active-class="text-blue-600 font-semibold" class="text-lg text-gray-700 hover:text-blue-600 transition-colors duration-200">
                Overview
              </NuxtLink>
            </li>
            <li class="mb-4">
              <NuxtLink to="/dashboard/favorites" active-class="text-blue-600 font-semibold" class="text-lg text-gray-700 hover:text-blue-600 transition-colors duration-200">
                My Favorites
              </NuxtLink>
            </li>
            <li class="mb-4">
              <NuxtLink to="/dashboard/listings" active-class="text-blue-600 font-semibold" class="text-lg text-gray-700 hover:text-blue-600 transition-colors duration-200">
                My Listings
              </NuxtLink>
            </li>
            <li class="mb-4">
              <NuxtLink to="/dashboard/profile" active-class="text-blue-600 font-semibold" class="text-lg text-gray-700 hover:text-blue-600 transition-colors duration-200">
                Profile Settings
              </NuxtLink>
            </li>
            <li>
              <button @click="logout" class="text-lg text-red-600 hover:text-red-800 transition-colors duration-200">
                Logout
              </button>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content Area (NuxtPage renders child routes) -->
      <main class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
        <NuxtPage />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'; // Will be created in Step 8
import { useRouter } from '#app';

const authStore = useAuthStore();
const router = useRouter();

// Middleware to protect this route
definePageMeta({
  middleware: ['auth'], // Create an auth middleware in middleware/auth.ts
});

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

useHead({
  title: 'User Dashboard - Real Estate',
  meta: [
    { name: 'description', content: 'Manage your favorite properties, listings, and profile settings.' },
  ],
});
</script>