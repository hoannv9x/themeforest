<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Properties Management</h1>
      <NuxtLink to="/dashboard/listings/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        Add New Property
      </NuxtLink>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="property in properties" :key="property.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <img :src="property.thumbnail || '/images/default-property.jpg'" alt="Property" class="w-12 h-12 rounded object-cover mr-3 border border-gray-200" />
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ property.title }}</div>
                  <div class="text-xs text-gray-500">{{ property.address }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ property.agent?.name }}</div>
              <div class="text-xs text-gray-500">{{ property.agent?.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-semibold text-gray-900">{{ formatPrice(property.price) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(property.status)">
                {{ property.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="approveProperty(property.id)" v-if="property.status === 'pending'" class="text-green-600 hover:text-green-900 mr-4">Approve</button>
              <NuxtLink :to="`/dashboard/listings/${property.id}/edit`" class="text-blue-600 hover:text-blue-900 mr-4">Edit</NuxtLink>
              <button @click="deleteProperty(property.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="properties.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No properties found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';
import { formatPrice } from '~/utils/format';

definePageMeta({
  layout: 'admin',
  middleware: ['admin'],
});

const { $api } = useNuxtApp();
const properties = ref([]);

const fetchProperties = async () => {
  try {
    const response = await $api.get('/admin/properties');
    properties.value = response.data;
  } catch (error) {
    console.error('Error fetching properties:', error);
  }
};

const approveProperty = async (id) => {
  if (confirm('Approve this property listing?')) {
    try {
      await $api.post(`/admin/properties/${id}/approve`);
      fetchProperties();
    } catch (error) {
      console.error('Error approving property:', error);
    }
  }
};

const deleteProperty = async (id) => {
  if (confirm('Are you sure you want to delete this property?')) {
    try {
      await $api.delete(`/admin/properties/${id}`);
      fetchProperties();
    } catch (error) {
      console.error('Error deleting property:', error);
    }
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'published': return 'bg-green-100 text-green-800';
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'sold': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

onMounted(fetchProperties);
</script>
