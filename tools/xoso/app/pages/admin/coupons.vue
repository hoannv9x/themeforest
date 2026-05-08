<script setup>
definePageMeta({
  middleware: ['admin'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin - Coupons',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const query = reactive({
  search: '',
  source: '',
  is_active: '',
  page: 1,
});

const coupons = ref([]);
const pagination = ref(null);
const selected = ref(null);

const createForm = reactive({
  code: '',
  discount_percent: 5,
  max_uses: 1,
  user_id: '',
  starts_at: '',
  expires_at: '',
  is_active: true,
});

const editForm = reactive({
  code: '',
  discount_percent: 5,
  max_uses: '',
  user_id: '',
  starts_at: '',
  expires_at: '',
  is_active: true,
  source: '',
});

const resetMessages = () => {
  errorMessage.value = '';
  successMessage.value = '';
};

const fetchCoupons = async () => {
  resetMessages();
  loading.value = true;
  try {
    const params = { ...query };
    if (params.is_active === '') delete params.is_active;
    const res = await api.adminGetCoupons(params);
    coupons.value = res.data?.data || [];
    pagination.value = res.data || null;
    if (selected.value) {
      const refreshed = coupons.value.find((c) => c.id === selected.value.id);
      if (refreshed) selected.value = refreshed;
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được danh sách coupon.';
  } finally {
    loading.value = false;
  }
};

const pick = async (row) => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminGetCoupon(row.id);
    selected.value = res.data || null;
    editForm.code = selected.value?.code || '';
    editForm.discount_percent = selected.value?.discount_percent || 5;
    editForm.max_uses = selected.value?.max_uses ?? '';
    editForm.user_id = selected.value?.user_id ?? '';
    editForm.starts_at = selected.value?.starts_at ? String(selected.value.starts_at).slice(0, 10) : '';
    editForm.expires_at = selected.value?.expires_at ? String(selected.value.expires_at).slice(0, 10) : '';
    editForm.is_active = !!selected.value?.is_active;
    editForm.source = selected.value?.source || '';
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được chi tiết coupon.';
  } finally {
    loading.value = false;
  }
};

const buildCreatePayload = () => ({
  code: createForm.code || undefined,
  discount_percent: Number(createForm.discount_percent),
  max_uses: createForm.max_uses ? Number(createForm.max_uses) : null,
  user_id: createForm.user_id ? Number(createForm.user_id) : null,
  starts_at: createForm.starts_at || null,
  expires_at: createForm.expires_at || null,
  is_active: !!createForm.is_active,
});

const createCoupon = async () => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminCreateCoupon(buildCreatePayload());
    successMessage.value = res.data?.message || 'Đã tạo coupon.';
    createForm.code = '';
    await fetchCoupons();
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Tạo coupon thất bại.';
  } finally {
    loading.value = false;
  }
};

const buildUpdatePayload = () => ({
  code: editForm.code || undefined,
  discount_percent: Number(editForm.discount_percent),
  max_uses: editForm.max_uses === '' ? null : Number(editForm.max_uses),
  user_id: editForm.user_id === '' ? null : Number(editForm.user_id),
  starts_at: editForm.starts_at || null,
  expires_at: editForm.expires_at || null,
  is_active: !!editForm.is_active,
  source: editForm.source || undefined,
});

const updateCoupon = async () => {
  if (!selected.value) return;
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminUpdateCoupon(selected.value.id, buildUpdatePayload());
    successMessage.value = res.data?.message || 'Đã cập nhật coupon.';
    selected.value = res.data?.coupon || selected.value;
    await fetchCoupons();
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Cập nhật coupon thất bại.';
  } finally {
    loading.value = false;
  }
};

watch(
  () => query.page,
  async () => {
    await fetchCoupons();
  }
);

onMounted(fetchCoupons);
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Coupon</h1>
        <p class="text-sm text-gray-600 mt-1">Tạo và cập nhật coupon (manual / referral / system).</p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input
          v-model="query.search"
          type="text"
          class="border rounded-lg px-3 py-2 text-sm"
          placeholder="Tìm code / email / tên"
          @keyup.enter="() => { query.page = 1; fetchCoupons(); }"
        />
        <select v-model="query.source" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchCoupons(); }">
          <option value="">Tất cả source</option>
          <option value="manual">manual</option>
          <option value="referral">referral</option>
          <option value="system">system</option>
        </select>
        <select v-model="query.is_active" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchCoupons(); }">
          <option value="">Tất cả trạng thái</option>
          <option :value="true">active</option>
          <option :value="false">inactive</option>
        </select>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="() => { query.page = 1; fetchCoupons(); }"
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
            Danh sách ({{ pagination?.total || coupons.length }})
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
                <th class="text-left px-4 py-2">Code</th>
                <th class="text-left px-4 py-2">%</th>
                <th class="text-left px-4 py-2">Uses</th>
                <th class="text-left px-4 py-2">User</th>
                <th class="text-left px-4 py-2">Active</th>
                <th class="text-left px-4 py-2">Source</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in coupons"
                :key="row.id"
                class="border-t hover:bg-gray-50 cursor-pointer"
                @click="pick(row)"
              >
                <td class="px-4 py-2 font-mono text-xs">{{ row.id }}</td>
                <td class="px-4 py-2 font-mono">{{ row.code }}</td>
                <td class="px-4 py-2">{{ row.discount_percent }}</td>
                <td class="px-4 py-2">{{ row.used_count }} / {{ row.max_uses ?? '∞' }}</td>
                <td class="px-4 py-2">{{ row.user?.email || '-' }}</td>
                <td class="px-4 py-2">
                  <span
                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ row.is_active ? 'active' : 'inactive' }}
                  </span>
                </td>
                <td class="px-4 py-2">{{ row.source }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t flex items-center justify-between text-sm">
          <button
            class="border rounded-lg px-3 py-1 disabled:opacity-50"
            :disabled="loading || (pagination && pagination.current_page <= 1)"
            @click="() => { query.page = Math.max(1, (pagination?.current_page || query.page) - 1); }"
          >
            ← Prev
          </button>
          <button
            class="border rounded-lg px-3 py-1 disabled:opacity-50"
            :disabled="loading || (pagination && pagination.current_page >= pagination.last_page)"
            @click="() => { query.page = (pagination?.current_page || query.page) + 1; }"
          >
            Next →
          </button>
        </div>
      </div>

      <div class="space-y-4">
        <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
          <div class="text-sm font-semibold text-gray-900 mb-3">Tạo coupon</div>
          <div class="space-y-3 text-sm">
            <input v-model="createForm.code" type="text" class="border rounded-lg px-3 py-2 w-full" placeholder="Code (để trống sẽ auto-generate)" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="createForm.discount_percent" type="number" min="1" max="100" class="border rounded-lg px-3 py-2 w-full" placeholder="%" />
              <input v-model="createForm.max_uses" type="number" min="1" class="border rounded-lg px-3 py-2 w-full" placeholder="Max uses" />
            </div>
            <input v-model="createForm.user_id" type="number" min="1" class="border rounded-lg px-3 py-2 w-full" placeholder="User ID (optional)" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="createForm.starts_at" type="date" class="border rounded-lg px-3 py-2 w-full" />
              <input v-model="createForm.expires_at" type="date" class="border rounded-lg px-3 py-2 w-full" />
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
              <input v-model="createForm.is_active" type="checkbox" />
              <span>Active</span>
            </label>
            <button class="w-full bg-green-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50" :disabled="loading" @click="createCoupon">
              {{ loading ? 'Đang tạo...' : 'Tạo' }}
            </button>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 p-4" v-if="selected">
          <div class="text-sm font-semibold text-gray-900 mb-3">Chi tiết coupon</div>
          <div class="text-xs text-gray-500 mb-2">ID: <span class="font-mono text-gray-800">{{ selected.id }}</span></div>
          <div class="space-y-3 text-sm">
            <input v-model="editForm.code" type="text" class="border rounded-lg px-3 py-2 w-full" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="editForm.discount_percent" type="number" min="1" max="100" class="border rounded-lg px-3 py-2 w-full" />
              <input v-model="editForm.max_uses" type="number" min="1" class="border rounded-lg px-3 py-2 w-full" placeholder="Max uses (empty = null)" />
            </div>
            <input v-model="editForm.user_id" type="number" min="1" class="border rounded-lg px-3 py-2 w-full" placeholder="User ID (empty = null)" />
            <div class="grid grid-cols-2 gap-2">
              <input v-model="editForm.starts_at" type="date" class="border rounded-lg px-3 py-2 w-full" />
              <input v-model="editForm.expires_at" type="date" class="border rounded-lg px-3 py-2 w-full" />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <select v-model="editForm.source" class="border rounded-lg px-3 py-2 text-sm w-full">
                <option value="">(keep)</option>
                <option value="manual">manual</option>
                <option value="referral">referral</option>
                <option value="system">system</option>
              </select>
              <label class="inline-flex items-center gap-2 text-sm">
                <input v-model="editForm.is_active" type="checkbox" />
                <span>Active</span>
              </label>
            </div>
            <button class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50" :disabled="loading" @click="updateCoupon">
              {{ loading ? 'Đang lưu...' : 'Lưu' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

