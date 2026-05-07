<script setup>
definePageMeta({
  middleware: ['admin'],
});

const url = useRequestURL();
const canonical = url.origin + url.pathname;
useSeoMeta({
  title: 'Admin - Payments',
});
useHead({
  link: [{ rel: 'canonical', href: canonical }],
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
});

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const query = reactive({
  search: '',
  type: '',
  status: '',
  manual_review_status: '',
  page: 1,
});

const payments = ref([]);
const pagination = ref(null);
const selected = ref(null);

const selectedFromQueryLoading = ref(false);

const resetMessages = () => {
  errorMessage.value = '';
  successMessage.value = '';
};

const setPaymentQuery = async (paymentId) => {
  const current = route.query?.payment ? String(route.query.payment) : '';
  const next = paymentId ? String(paymentId) : '';
  if (current === next) {
    return;
  }

  const nextQuery = { ...route.query };
  if (!next) {
    delete nextQuery.payment;
  } else {
    nextQuery.payment = next;
  }

  await router.replace({ query: nextQuery });
};

const fetchSelectedById = async (paymentId) => {
  if (!paymentId) return;
  selectedFromQueryLoading.value = true;
  try {
    const res = await api.adminGetPayment(paymentId);
    selected.value = res.data || null;
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được payment theo query.';
  } finally {
    selectedFromQueryLoading.value = false;
  }
};

const fetchPayments = async () => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminGetPayments({ ...query });
    payments.value = res.data?.data || [];
    pagination.value = res.data || null;
    if (selected.value) {
      const refreshed = payments.value.find((p) => p.id === selected.value.id);
      if (refreshed) {
        selected.value = refreshed;
      }
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được danh sách payment.';
  } finally {
    loading.value = false;
  }
};

const pick = async (row) => {
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminGetPayment(row.id);
    selected.value = res.data || null;
    await setPaymentQuery(row.id);
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Không tải được chi tiết payment.';
  } finally {
    loading.value = false;
  }
};

const approve = async () => {
  if (!selected.value) return;
  resetMessages();
  loading.value = true;
  try {
    const res = await api.adminApprovePayment(selected.value.id);
    successMessage.value = res.data?.message || 'Đã approve.';
    selected.value = res.data?.payment || selected.value;
    await fetchPayments();
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'Approve thất bại.';
  } finally {
    loading.value = false;
  }
};

const statusBadgeClass = (p) => {
  if (p.status === 'paid') return 'bg-green-100 text-green-800';
  if (p.status === 'failed') return 'bg-red-100 text-red-800';
  if (p.status === 'expired') return 'bg-gray-100 text-gray-700';
  return 'bg-yellow-100 text-yellow-800';
};

const manualBadgeClass = (p) => {
  if (p.manual_review_status === 'requested') return 'bg-blue-100 text-blue-800';
  return 'bg-gray-100 text-gray-700';
};

watch(
  () => query.page,
  fetchPayments
);

watch(
  () => route.query.payment,
  async (value) => {
    const id = value ? Number(value) : null;
    if (!id || Number.isNaN(id)) {
      selected.value = null;
      return;
    }
    if (selected.value?.id === id) {
      return;
    }
    await fetchSelectedById(id);
  },
  { immediate: true }
);

onMounted(async () => {
  await fetchPayments();

  const id = route.query.payment ? Number(route.query.payment) : null;
  if (id && !Number.isNaN(id) && !selected.value) {
    await fetchSelectedById(id);
  }
});
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý Payment</h1>
        <p class="text-sm text-gray-600 mt-1">Duyệt thanh toán và theo dõi trạng thái.</p>
      </div>
      <NuxtLink to="/admin" class="text-sm text-blue-600 hover:underline">← Admin</NuxtLink>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input
          v-model="query.search"
          type="text"
          class="border rounded-lg px-3 py-2 text-sm"
          placeholder="Tìm email / tên / transfer_content"
          @keyup.enter="() => { query.page = 1; fetchPayments(); }"
        />
        <select v-model="query.type" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchPayments(); }">
          <option value="">Tất cả type</option>
          <option value="vip">vip</option>
          <option value="api">api</option>
        </select>
        <select v-model="query.status" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchPayments(); }">
          <option value="">Tất cả status</option>
          <option value="pending">pending</option>
          <option value="paid">paid</option>
          <option value="expired">expired</option>
          <option value="failed">failed</option>
        </select>
        <select v-model="query.manual_review_status" class="border rounded-lg px-3 py-2 text-sm" @change="() => { query.page = 1; fetchPayments(); }">
          <option value="">Manual review (all)</option>
          <option value="none">none</option>
          <option value="requested">requested</option>
        </select>
        <button
          class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
          :disabled="loading"
          @click="() => { query.page = 1; fetchPayments(); }"
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
            Danh sách ({{ pagination?.total || payments.length }})
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
                <th class="text-left px-4 py-2">User</th>
                <th class="text-left px-4 py-2">Gói</th>
                <th class="text-left px-4 py-2">Status</th>
                <th class="text-left px-4 py-2">Manual</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in payments"
                :key="p.id"
                class="border-t hover:bg-gray-50 cursor-pointer"
                :class="selected?.id === p.id ? 'bg-blue-50' : ''"
                @click="() => pick(p)"
              >
                <td class="px-4 py-2 font-mono">{{ p.id }}</td>
                <td class="px-4 py-2">
                  <div class="text-gray-900">{{ p.user?.email || '-' }}</div>
                  <div class="text-xs text-gray-500">{{ p.user?.name || '-' }}</div>
                </td>
                <td class="px-4 py-2">
                  <div class="text-gray-900">{{ p.plan_name }}</div>
                  <div class="text-xs text-gray-500">{{ p.type }} · {{ Number(p.amount).toLocaleString('vi-VN') }}đ</div>
                </td>
                <td class="px-4 py-2">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" :class="statusBadgeClass(p)">
                    {{ p.status }}
                  </span>
                </td>
                <td class="px-4 py-2">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" :class="manualBadgeClass(p)">
                    {{ p.manual_review_status || 'none' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!payments.length">
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
        <div v-if="!selected" class="text-sm text-gray-500 mt-2">Chọn 1 payment để xem.</div>

        <div v-else class="mt-3 space-y-3">
          <div class="text-xs text-gray-500">
            ID: <span class="font-mono text-gray-800">{{ selected.id }}</span>
          </div>

          <div class="text-sm">
            <div><span class="text-gray-500">User:</span> <span class="text-gray-900">{{ selected.user?.email }}</span></div>
            <div class="text-xs text-gray-500">{{ selected.user?.name }}</div>
          </div>

          <div class="text-sm space-y-1">
            <div><span class="text-gray-500">Plan:</span> <span class="text-gray-900">{{ selected.plan_name }}</span></div>
            <div><span class="text-gray-500">Type:</span> <span class="text-gray-900">{{ selected.type }}</span></div>
            <div><span class="text-gray-500">Amount:</span> <span class="text-gray-900">{{ Number(selected.amount).toLocaleString('vi-VN') }}đ</span></div>
          </div>

          <div class="text-sm space-y-1">
            <div>
              <span class="text-gray-500">Status:</span>
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ml-2" :class="statusBadgeClass(selected)">
                {{ selected.status }}
              </span>
            </div>
            <div>
              <span class="text-gray-500">Manual review:</span>
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ml-2" :class="manualBadgeClass(selected)">
                {{ selected.manual_review_status || 'none' }}
              </span>
            </div>
            <div v-if="selected.manual_review_requested_at" class="text-xs text-gray-600">
              Requested at: <span class="font-mono">{{ selected.manual_review_requested_at }}</span>
            </div>
            <div v-if="selected.paid_at" class="text-xs text-gray-600">
              Paid at: <span class="font-mono">{{ selected.paid_at }}</span>
            </div>
          </div>

          <div class="text-sm">
            <div class="text-gray-500">Transfer content</div>
            <div class="font-mono bg-gray-50 border rounded p-2 text-xs break-all">{{ selected.transfer_content }}</div>
          </div>

          <button
            class="w-full bg-green-600 text-white rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
            :disabled="loading || selected.status === 'paid'"
            @click="approve"
          >
            {{ selected.status === 'paid' ? 'Đã paid' : 'Approve (set paid)' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
