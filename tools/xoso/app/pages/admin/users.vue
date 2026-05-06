<script setup>
definePageMeta({
  middleware: ['admin'],
});

const api = useApi();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const query = reactive({
  search: '',
  role: '',
  permission: '',
  page: 1,
});

const users = ref([]);
const pagination = ref(null);

const selectedUser = ref(null);
const form = reactive({
  name: '',
  email: '',
  role: '',
  permission: '',
  vip_days: '',
  api_days: '',
  vip_expired_at: '',
  api_expired_at: '',
});

const resetMessages = () => {
  errorMessage.value = '';
  successMessage.value = '';
};

const pickUser = (user) => {
  selectedUser.value = user;
  form.name = user?.name || '';
  form.email = user?.email || '';
  form.role = user?.role || '';
  form.permission = user?.permission || '';
  form.vip_days = '';
  form.api_days = '';
  form.vip_expired_at = user?.vip_expired_at ? String(user.vip_expired_at).slice(0, 10) : '';
  form.api_expired_at = user?.api_expired_at ? String(user.api_expired_at).slice(0, 10) : '';
};

const fetchUsers = async () => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminGetUsers({ ...query });
    users.value = res.data.data || [];
    pagination.value = res.data || null;
    if (selectedUser.value) {
      const refreshed = users.value.find((u) => u.id === selectedUser.value.id);
      if (refreshed) {
        pickUser(refreshed);
      }
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được danh sách user.';
  } finally {
    loading.value = false;
  }
};

const buildPayload = () => {
  const payload = {};
  if (form.name !== (selectedUser.value?.name || '')) payload.name = form.name;
  if (form.email !== (selectedUser.value?.email || '')) payload.email = form.email;
  if (form.role && form.role !== (selectedUser.value?.role || '')) payload.role = form.role;
  if (form.permission && form.permission !== (selectedUser.value?.permission || '')) payload.permission = form.permission;
  if (form.vip_days) payload.vip_days = Number(form.vip_days);
  if (form.api_days) payload.api_days = Number(form.api_days);

  const currentVipDate = selectedUser.value?.vip_expired_at ? String(selectedUser.value.vip_expired_at).slice(0, 10) : '';
  const currentApiDate = selectedUser.value?.api_expired_at ? String(selectedUser.value.api_expired_at).slice(0, 10) : '';
  if (form.vip_expired_at !== currentVipDate) payload.vip_expired_at = form.vip_expired_at || null;
  if (form.api_expired_at !== currentApiDate) payload.api_expired_at = form.api_expired_at || null;
  return payload;
};

const saveUser = async () => {
  resetMessages();
  if (!selectedUser.value) return;
  const payload = buildPayload();
  if (Object.keys(payload).length === 0) {
    successMessage.value = 'Không có thay đổi để lưu.';
    return;
  }

  loading.value = true;
  try {
    const res = await api.adminUpdateUser(selectedUser.value.id, payload);
    successMessage.value = res.data?.message || 'Đã cập nhật user.';
    await fetchUsers();
  } catch (e) {
    const msg = e?.response?.data?.message || e?.message || 'Cập nhật thất bại.';
    errorMessage.value = msg;
  } finally {
    loading.value = false;
  }
};

const quickGrantVip = async (days) => {
  if (!selectedUser.value) return;
  form.vip_days = String(days);
  await saveUser();
};

const quickRevokeVip = async () => {
  if (!selectedUser.value) return;
  form.vip_days = '';
  form.vip_expired_at = '';
  form.role = 'user';
  await saveUser();
};

const quickSetDeveloper = async () => {
  if (!selectedUser.value) return;
  form.permission = 'developer';
  await saveUser();
};

const quickUnsetDeveloper = async () => {
  if (!selectedUser.value) return;
  form.permission = 'user';
  await saveUser();
};

watch(
  () => query.page,
  async () => {
    await fetchUsers();
  }
);

onMounted(fetchUsers);
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý User</h1>
        <p class="text-sm text-gray-600 mt-1">Tìm kiếm và cập nhật trạng thái user.</p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          v-model="query.search"
          type="text"
          class="border rounded-lg px-3 py-2 text-sm"
          placeholder="Tìm theo email hoặc tên"
          @keyup.enter="() => { query.page = 1; fetchUsers(); }"
        />
        <select v-model="query.role" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchUsers(); }">
          <option value="">Tất cả role</option>
          <option value="user">user</option>
          <option value="vip">vip</option>
          <option value="admin">admin</option>
        </select>
        <select v-model="query.permission" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchUsers(); }">
          <option value="">Tất cả permission</option>
          <option value="user">user</option>
          <option value="developer">developer</option>
        </select>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="() => { query.page = 1; fetchUsers(); }"
        >
          {{ loading ? 'Đang tải...' : 'Tải danh sách' }}
        </button>
      </div>

      <div v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</div>
      <div v-if="successMessage" class="mt-3 text-sm text-green-700">{{ successMessage }}</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
          <div class="text-sm font-semibold text-gray-900">
            Danh sách ({{ pagination?.total || users.length }})
          </div>
          <div class="text-xs text-gray-500">
            Trang {{ pagination?.current_page || query.page }} / {{ pagination?.last_page || 1 }}
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700">
              <tr>
                <th class="text-left px-4 py-2">ID</th>
                <th class="text-left px-4 py-2">Email</th>
                <th class="text-left px-4 py-2">Tên</th>
                <th class="text-left px-4 py-2">Role</th>
                <th class="text-left px-4 py-2">Permission</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="u in users"
                :key="u.id"
                class="border-t hover:bg-gray-50 cursor-pointer"
                :class="selectedUser?.id === u.id ? 'bg-blue-50' : ''"
                @click="() => pickUser(u)"
              >
                <td class="px-4 py-2 font-mono">{{ u.id }}</td>
                <td class="px-4 py-2">{{ u.email }}</td>
                <td class="px-4 py-2">{{ u.name }}</td>
                <td class="px-4 py-2">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="u.role === 'vip' ? 'bg-yellow-100 text-yellow-800' : u.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ u.role }}
                  </span>
                </td>
                <td class="px-4 py-2">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="u.permission === 'developer' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ u.permission }}
                  </span>
                </td>
              </tr>
              <tr v-if="!users.length">
                <td class="px-4 py-4 text-center text-gray-500" colspan="5">Không có dữ liệu</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-4 py-3 border-t flex items-center justify-between">
          <button
            class="text-sm px-3 py-2 rounded-lg border disabled:opacity-40"
            :disabled="loading || (pagination?.current_page || 1) <= 1"
            @click="() => { query.page = (pagination?.current_page || 1) - 1; }"
          >
            ← Trước
          </button>
          <button
            class="text-sm px-3 py-2 rounded-lg border disabled:opacity-40"
            :disabled="loading || (pagination?.current_page || 1) >= (pagination?.last_page || 1)"
            @click="() => { query.page = (pagination?.current_page || 1) + 1; }"
          >
            Sau →
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
        <div class="text-sm font-semibold text-gray-900">Chi tiết</div>
        <div v-if="!selectedUser" class="text-sm text-gray-500 mt-2">
          Chọn 1 user để chỉnh sửa.
        </div>

        <div v-else class="mt-3 space-y-3">
          <div class="text-xs text-gray-500">ID: <span class="font-mono text-gray-800">{{ selectedUser.id }}</span></div>

          <div class="space-y-1">
            <div class="text-xs font-semibold text-gray-700">Tên</div>
            <input v-model="form.name" type="text" class="border rounded-lg px-3 py-2 text-sm w-full" />
          </div>

          <div class="space-y-1">
            <div class="text-xs font-semibold text-gray-700">Email</div>
            <input v-model="form.email" type="email" class="border rounded-lg px-3 py-2 text-sm w-full" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">Role</div>
              <select v-model="form.role" class="border rounded-lg px-3 py-2 text-sm w-full">
                <option value="user">user</option>
                <option value="vip">vip</option>
                <option value="admin">admin</option>
              </select>
            </div>
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">Permission</div>
              <select v-model="form.permission" class="border rounded-lg px-3 py-2 text-sm w-full">
                <option value="user">user</option>
                <option value="developer">developer</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">VIP hết hạn</div>
              <input v-model="form.vip_expired_at" type="date" class="border rounded-lg px-3 py-2 text-sm w-full" />
            </div>
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">API hết hạn</div>
              <input v-model="form.api_expired_at" type="date" class="border rounded-lg px-3 py-2 text-sm w-full" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">Cộng VIP (ngày)</div>
              <input v-model="form.vip_days" type="number" min="1" class="border rounded-lg px-3 py-2 text-sm w-full" placeholder="Ví dụ 30" />
            </div>
            <div class="space-y-1">
              <div class="text-xs font-semibold text-gray-700">Cộng API (ngày)</div>
              <input v-model="form.api_days" type="number" min="1" class="border rounded-lg px-3 py-2 text-sm w-full" placeholder="Ví dụ 30" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <button
              class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
              :disabled="loading"
              @click="saveUser"
            >
              Lưu
            </button>
            <button
              class="border rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
              :disabled="loading"
              @click="() => pickUser(selectedUser)"
            >
              Reset
            </button>
          </div>

          <div class="pt-2 border-t space-y-2">
            <div class="text-xs font-semibold text-gray-700">Thao tác nhanh</div>
            <div class="grid grid-cols-2 gap-2">
              <button class="border rounded-lg px-3 py-2 text-xs font-semibold" :disabled="loading" @click="() => quickGrantVip(7)">
                + VIP 7 ngày
              </button>
              <button class="border rounded-lg px-3 py-2 text-xs font-semibold" :disabled="loading" @click="() => quickGrantVip(30)">
                + VIP 30 ngày
              </button>
              <button class="border rounded-lg px-3 py-2 text-xs font-semibold" :disabled="loading" @click="quickRevokeVip">
                Gỡ VIP
              </button>
              <button class="border rounded-lg px-3 py-2 text-xs font-semibold" :disabled="loading" @click="quickSetDeveloper">
                Set developer
              </button>
              <button class="border rounded-lg px-3 py-2 text-xs font-semibold" :disabled="loading" @click="quickUnsetDeveloper">
                Unset developer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

