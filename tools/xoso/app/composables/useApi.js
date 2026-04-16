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

    getResults: (params) => $api.get('/v1/results', { params }),

    getMostFrequentNumbers: (params) => $api.get('/v1/number/most-frequent', { params }),

  };
};
