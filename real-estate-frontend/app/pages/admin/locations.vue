<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Locations Management</h1>
      <button @click="openCreateModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        Add New Location
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properties Count</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="location in locations" :key="location.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ location.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ location.slug }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ location.type || 'City' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.properties_count }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editLocation(location)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
              <button @click="deleteLocation(location.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="locations.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No locations found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Location Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Location' : 'Add New Location' }}</h2>
        <form @submit.prevent="saveLocation">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input v-model="form.slug" type="text" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. ho-chi-minh-city" />
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="form.type" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
              <option value="city">City</option>
              <option value="district">District</option>
              <option value="ward">Ward</option>
            </select>
          </div>
          <div class="flex justify-end gap-3">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';

definePageMeta({
  layout: 'admin',
  middleware: ['admin'],
});

const { $api } = useNuxtApp();
const locations = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  name: '',
  slug: '',
  type: 'city',
});

const fetchLocations = async () => {
  try {
    const response = await $api.get('/admin/locations');
    locations.value = response.data;
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { id: null, name: '', slug: '', type: 'city' };
  showModal.value = true;
};

const editLocation = (location) => {
  isEditing.value = true;
  form.value = { ...location };
  showModal.value = true;
};

const saveLocation = async () => {
  try {
    if (isEditing.value) {
      await $api.put(`/admin/locations/${form.value.id}`, form.value);
    } else {
      await $api.post('/admin/locations', form.value);
    }
    showModal.value = false;
    fetchLocations();
  } catch (error) {
    console.error('Error saving location:', error);
  }
};

const deleteLocation = async (id) => {
  if (confirm('Are you sure you want to delete this location?')) {
    try {
      await $api.delete(`/admin/locations/${id}`);
      fetchLocations();
    } catch (error) {
      console.error('Error deleting location:', error);
    }
  }
};

onMounted(fetchLocations);
</script>
