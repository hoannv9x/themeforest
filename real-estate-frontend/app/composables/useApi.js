/**
 * @description Một composable tập trung các dịch vụ API cho ứng dụng Nuxt.
 * Nó đóng gói tất cả các lệnh gọi API dựa trên Axios, giúp chúng có thể tái sử dụng và dễ quản lý hơn.
 * Composable này sẽ được Nuxt tự động import.
 */
export const useApi = () => {
  // Lấy instance $api đã được cấu hình trong plugin của Nuxt
  const { $api } = useNuxtApp();

  return {
    /**
     * Lấy danh sách môi giới có phân trang.
     * @param {object} params - Các tham số truy vấn (ví dụ: page, per_page).
     * @returns {Promise}
     */
    getAgents: (params = {}) => $api.get('/agents', { params }),

    /**
     * Lấy thông tin một môi giới theo ID.
     * @param {string|number} id - ID của môi giới.
     * @returns {Promise}
     */
    getAgent: (id) => $api.get(`/agents/${id}`),

    /**
     * Lấy danh sách bất động sản của một môi giới cụ thể, có phân trang.
     * @param {string|number} agentId - ID của môi giới.
     * @param {object} params - Các tham số truy vấn (ví dụ: page, per_page).
     * @returns {Promise}
     */
    getAgentProperties: (agentId, params = {}) => $api.get(`/agents/${agentId}/properties`, { params }),

    /**
     * Lấy danh sách bất động sản có phân trang và bộ lọc.
     * @param {object} params - Các tham số để lọc và phân trang.
     * @returns {Promise}
     */
    getProperties: (params = {}) => $api.get('/properties', { params }),

    /**
     * Lấy thông tin một bất động sản theo ID.
     * @param {string|number} id - ID của bất động sản.
     * @returns {Promise}
     */
    getProperty: (id) => $api.get(`/properties/${id}`),

    /**
     * Lấy danh sách tất cả Tỉnh/Thành phố.
     * @returns {Promise}
     */
    getCities: () => $api.get('/cities'),

    /**
     * Lấy danh sách Quận/Huyện theo một Tỉnh/Thành phố.
     * @param {string|number} cityId - ID của Tỉnh/Thành phố.
     * @returns {Promise}
     */
    getDistrictsByCity: (cityId) => $api.get(`/cities/${cityId}/districts`),

    /**
     * Lấy danh sách các loại hình bất động sản.
     * @returns {Promise}
     */
    getPropertyTypes: () => $api.get('/property-types'),
  };
};
