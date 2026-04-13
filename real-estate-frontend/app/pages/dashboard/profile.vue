<template>
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Profile Settings</h2>
    <div v-if="user" class="bg-gray-100 p-6 rounded-lg shadow-inner">
      <form @submit.prevent="updateProfile">
        <div class="mb-4">
          <label for="name" class="block text-gray-700 text-sm font-bold mb-2"
            >Name:</label
          >
          <input
            type="text"
            id="name"
            v-model="user.name"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          />
        </div>
        <div class="mb-4">
          <label for="email" class="block text-gray-700 text-sm font-bold mb-2"
            >Email:</label
          >
          <input
            type="email"
            id="email"
            v-model="user.email"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            disabled
          />
        </div>
        <div class="mb-4">
          <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2"
            >Phone Number:</label
          >
          <input
            type="text"
            id="phone_number"
            v-model="user.phone_number"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          />
        </div>
        <div class="mb-6">
          <label for="address" class="block text-gray-700 text-sm font-bold mb-2"
            >Address:</label
          >
          <input
            type="text"
            id="address"
            v-model="user.address"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          />
        </div>
        <button
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
        >
          Update Profile
        </button>
      </form>
    </div>
    <div v-else class="text-center text-gray-600 py-10">Loading user profile...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useNuxtApp } from "#app";
import { useAuthStore } from "~/stores/auth";

const { $api } = useNuxtApp();
const authStore = useAuthStore();
const user = ref(null);

const fetchUserProfile = async () => {
  try {
    const response = await $api.get("/user"); // Assuming an API endpoint to get the authenticated user's profile
    user.value = response.data.user;
  } catch (error) {
    console.error("Error fetching user profile:", error);
  }
};

const updateProfile = async () => {
  try {
    await $api.put("/user/profile", user.value); // Assuming an API endpoint to update user profile
    alert("Profile updated successfully!");
    // Optionally, update the user in the auth store
    authStore.setUser(user.value);
  } catch (error) {
    console.error("Error updating profile:", error);
    alert("Failed to update profile.");
  }
};

onMounted(fetchUserProfile);
</script>
