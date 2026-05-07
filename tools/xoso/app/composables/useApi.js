/**
 * @description Một composable tập trung các dịch vụ API cho ứng dụng Nuxt.
 * Nó đóng gói tất cả các lệnh gọi API dựa trên Axios, giúp chúng có thể tái sử dụng và dễ quản lý hơn.
 * Composable này sẽ được Nuxt tự động import.
 */
export const useApi = () => {
  // Lấy instance $api đã được cấu hình trong plugin của Nuxt
  const { $api } = useNuxtApp();

  return {
    getStats: () => $api.get('/v1/stats'),

    getPredictions: () => $api.get('/v1/predictions'),
    getYesterdayPredictions: () => $api.get('/v1/predictions/yesterday'),
    getMiniGameOverview: () => $api.get('/v1/mini-game'),
    getMiniGameMe: () => $api.get('/v1/mini-game/me'),
    submitMiniGamePrediction: (payload) => $api.post('/v1/mini-game/predict', payload),
    submitMiniGamePayoutRequest: (payload) => $api.post('/v1/mini-game/payout-request', payload),

    getResults: (params) => $api.get('/v1/results', { params }),

    getMostFrequentNumbers: (params) => $api.get('/v1/number/most-frequent', { params }),

    getVipPredictions: () => $api.get('/v1/vip/predictions'),
    getVipYesterdayPredictions: () => $api.get('/v1/vip/predictions/yesterday'),
    getVipStatus: () => $api.get('/v1/vip/status'),
    getVipUpsell: () => $api.get('/v1/vip/upsell'),
    startVipTrial: () => $api.post('/v1/vip/start-trial'),

    getMe: () => $api.get('/v1/me'),
    getPaymentPlans: () => $api.get('/v1/payments/plans'),
    createPayment: (payload) => $api.post('/v1/payments', payload),
    getPaymentHistory: () => $api.get('/v1/payments/history'),
    getPaymentStatus: (paymentId) => $api.get(`/v1/payments/${paymentId}/status`),
    completePayment: (paymentId) => $api.post(`/v1/payments/${paymentId}/paid`),
    getApiSubscription: () => $api.get('/v1/api/subscription'),
    getApiWebhooks: () => $api.get('/v1/api/webhooks'),
    createApiWebhook: (payload) => $api.post('/v1/api/webhooks', payload),
    updateApiWebhook: (id, payload) => $api.put(`/v1/api/webhooks/${id}`, payload),
    deleteApiWebhook: (id) => $api.delete(`/v1/api/webhooks/${id}`),
    markPaymentPaid: (payload) => $api.post(`/v1/payments/bank-notify`, payload),

    adminGetUsers: (params) => $api.get('/v1/admin/users', { params }),
    adminGetUser: (id) => $api.get(`/v1/admin/users/${id}`),
    adminUpdateUser: (id, payload) => $api.put(`/v1/admin/users/${id}`, payload),

    adminGetResults: (params) => $api.get('/v1/admin/results', { params }),
    adminGetResultByDate: (date, params) => $api.get(`/v1/admin/results/by-date/${date}`, { params }),
    adminUpsertResultByDate: (date, payload) => $api.put(`/v1/admin/results/by-date/${date}`, payload),

    adminGetPayments: (params) => $api.get('/v1/admin/payments', { params }),
    adminGetPayment: (id) => $api.get(`/v1/admin/payments/${id}`),
    adminApprovePayment: (id) => $api.post(`/v1/admin/payments/${id}/approve`),
  };
};
