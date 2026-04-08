<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Users Management</h1>
      <button @click="openCreateModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        Add New User
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avatar</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <img :src="user.avatar || avatarDefault" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-gray-200" />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ user.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'">
                {{ user.role }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editUser(user)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
              <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- User Modal (Simplified) -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit User' : 'Add New User' }}</h2>
        <form @submit.prevent="saveUser">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required />
          </div>
          <div class="mb-4" v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="form.password" type="password" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="form.role" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
              <option value="user">User</option>
              <option value="agent">Agent</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>
            <div v-if="form.avatar_url" class="mb-2">
              <img :src="form.avatar_url" alt="Current Avatar" class="w-20 h-20 rounded-full object-cover border" />
            </div>
            <file-pond
              name="avatar"
              ref="pond"
              label-idle="Drop image here or <span class='filepond--label-action'>Browse</span>"
              :allow-multiple="false"
              accepted-file-types="image/jpeg, image/png"
              :server="filePondServer"
              @processfile="onProcessFile"
              @removefile="onRemoveFile"
            />
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
import { ref, onMounted, computed } from 'vue';
import { useNuxtApp, useCookie, useRuntimeConfig } from '#app';
import avatarDefault from '~/assets/images/default-avatar.jpg';

// Import FilePond
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

// Import FilePond plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

useHead({
  title: 'Admin Real Estate Listing - Find Your Dream Home',
  meta: [
    { name: 'description', content: 'Search properties for sale and rent. Find houses, apartments, condos, and land listings.' },
    { property: 'og:title', content: 'Real Estate Listing - Find Your Dream Home' },
    { property: 'og:description', content: 'Search properties for sale and rent. Find houses, apartments, condos, and land listings.' },
    { property: 'og:image', content: '~/assets/images/og-home.jpg' },
    { property: 'og:url', content: 'https://yourdomain.com/' },
  ],
});

// Create FilePond component
const FilePond = vueFilePond(
  FilePondPluginFileValidateType,
  FilePondPluginImagePreview,
  FilePondPluginFileValidateSize
);

definePageMeta({
  layout: 'admin',
  middleware: ['admin'],
});

const { $api } = useNuxtApp();
const config = useRuntimeConfig();
const adminToken = useCookie('admin_token');

const users = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const pond = ref(null);

const form = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  role: 'user',
  avatar: '', // This will hold the path for saving
  avatar_url: '', // This will hold the URL for preview
});

// FilePond Server Configuration
const filePondServer = computed(() => ({
  url: `${config.public.apiBaseUrl}/admin`,
  process: {
    url: '/upload',
    headers: {
      Authorization: `Bearer ${adminToken.value}`,
    },
    ondata: (formData) => {
      if (form.value.id) {
        formData.append('user_id', form.value.id);
      }
      formData.append('folder', 'avatars');
      return formData;
    },
    onload: (response) => {
      const data = JSON.parse(response);
      form.value.avatar = data.path; // Store the relative path in the form
      form.value.avatar_url = data.url; // Store the URL for preview
      return data.path;
    },
  },
  revert: {
    url: '/upload',
    headers: {
      Authorization: `Bearer ${adminToken.value}`,
    },
  },
}));

const onProcessFile = (error, file) => {
  if (error) {
    console.error('FilePond upload error:', error);
    return;
  }
};

const onRemoveFile = (error, file) => {
  if (!error) {
    // Clear avatar when user removes file during edit
    form.value.avatar = null;
    form.value.avatar_url = '';
  }
};

const fetchUsers = async () => {
  try {
    const response = await $api.get('/admin/users');
    users.value = response.data?.data || [];
    
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { id: null, name: '', email: '', password: '', role: 'user', avatar: '', avatar_url: '' };
  if (pond.value) {
    pond.value.removeFiles();
  }
  showModal.value = true;
};

const editUser = (user) => {
  isEditing.value = true;
  form.value = { 
    ...user, 
    avatar: user.avatar_path || '', // Original path for comparison/save
    avatar_url: user.avatar || '' // Full URL for preview
  };
  if (pond.value) {
    pond.value.removeFiles();
  }
  showModal.value = true;
};

const saveUser = async () => {
  try {
    if (isEditing.value) {
      await $api.put(`/admin/users/${form.value.id}`, form.value);
    } else {
      await $api.post('/admin/users', form.value);
    }
    showModal.value = false;
    fetchUsers();
  } catch (error) {
    console.error('Error saving user:', error);
  }
};

const deleteUser = async (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await $api.delete(`/admin/users/${id}`);
      fetchUsers();
    } catch (error) {
      console.error('Error deleting user:', error);
    }
  }
};

onMounted(fetchUsers);
</script>
