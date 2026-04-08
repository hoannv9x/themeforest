<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Admin Sidebar -->
    <aside class="w-64 bg-gray-800 text-white p-6">
      <h2 class="text-2xl font-bold mb-8">Admin Panel</h2>
      <nav>
        <ul>
          <li class="mb-4">
            <NuxtLink
              to="/admin/dashboard"
              active-class="text-blue-400"
              class="block py-2 px-3 rounded hover:bg-gray-700"
            >
              Dashboard
            </NuxtLink>
          </li>
          <li class="mb-4">
            <NuxtLink
              to="/admin/users"
              active-class="text-blue-400"
              class="block py-2 px-3 rounded hover:bg-gray-700"
            >
              Users
            </NuxtLink>
          </li>
          <li class="mb-4">
            <NuxtLink
              to="/admin/properties"
              active-class="text-blue-400"
              class="block py-2 px-3 rounded hover:bg-gray-700"
            >
              Properties
            </NuxtLink>
          </li>
          <li class="mb-4">
            <NuxtLink
              to="/admin/property-types"
              active-class="text-blue-400"
              class="block py-2 px-3 rounded hover:bg-gray-700"
            >
              Property Types
            </NuxtLink>
          </li>
          <li class="mb-4">
            <NuxtLink
              to="/admin/locations"
              active-class="text-blue-400"
              class="block py-2 px-3 rounded hover:bg-gray-700"
            >
              Locations
            </NuxtLink>
          </li>
          <li>
            <button
              @click="logout"
              class="block w-full text-left py-2 px-3 rounded hover:bg-gray-700 text-red-400"
            >
              Logout
            </button>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Admin Content -->
    <main class="flex-1 p-8">
      <slot />
    </main>
    <CommonLoading />
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";
import { useRouter } from "#app";
import CommonLoading from "~/components/common/Loading.vue";

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
  await authStore.adminLogout();
  router.push("/admin/login");
};
</script>
