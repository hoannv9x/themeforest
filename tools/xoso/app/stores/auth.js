import { defineStore } from 'pinia';
import { useNuxtApp, useCookie } from '#app';

export const useAuthStore = defineStore('auth', {
  state: () => {
    // Regular User
    const userToken = useCookie('sanctum_token');
    const userData = useCookie('user');
    const vipStatusData = useCookie('vip_status');

    return {
      // Regular User state
      user: userData.value || null,
      token: userToken.value || null,
      isAuthenticated: !!userToken.value,
      vipStatus: vipStatusData.value || null,
    }
  },

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    getIsAuthenticated: (state) => state.isAuthenticated,
    getVipStatus: (state) => state.vipStatus,
    isVip: (state) => state.vipStatus?.is_vip || false,
    isTrial: (state) => state.vipStatus?.is_trial || false,
  },

  actions: {
    setVipStatus(vipStatus) {
      this.vipStatus = vipStatus;
      const vipStatusCookie = useCookie('vip_status', { maxAge: 60 * 60 * 24 * 7, path: '/' });
      vipStatusCookie.value = vipStatus;
    },

    // Initialize auth state (can be used to re-sync cookies)
    initAuth() {
      // Cookies are already read in state(), but we can ensure reactivity here if needed
      const userToken = useCookie('sanctum_token');
      const userData = useCookie('user');
      const vipStatusData = useCookie('vip_status');

      this.token = userToken.value || null;
      this.user = userData.value || null;
      this.vipStatus = vipStatusData.value || null;
      this.isAuthenticated = !!userToken.value;
    },

    // User login
    async login(email, password) {
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/v1/login', { email, password });
        this.token = response.data.token;
        this.user = response.data.user;
        this.vipStatus = response.data.vip_status;
        this.isAuthenticated = true;

        const tokenCookie = useCookie('sanctum_token', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const vipStatusCookie = useCookie('vip_status', { maxAge: 60 * 60 * 24 * 7, path: '/' });

        tokenCookie.value = this.token;
        userCookie.value = this.user;
        vipStatusCookie.value = this.vipStatus;

        return response.data;
      } catch (error) {
        this.logout();
        throw new Error(error.response?.data?.message || 'Login failed');
      }
    },

    async register(dataOrName, email, password, password_confirmation) {
      const { $api } = useNuxtApp();
      let payload;
      
      if (typeof dataOrName === 'object') {
        payload = dataOrName;
      } else {
        payload = {
          name: dataOrName,
          email,
          password,
          password_confirmation
        };
      }
      
      try {
        const response = await $api.post('/v1/register', payload);
        this.token = response.data.token;
        this.user = response.data.user;
        this.vipStatus = response.data.vip_status;
        this.isAuthenticated = true;

        const tokenCookie = useCookie('sanctum_token', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const vipStatusCookie = useCookie('vip_status', { maxAge: 60 * 60 * 24 * 7, path: '/' });

        tokenCookie.value = this.token;
        userCookie.value = this.user;
        vipStatusCookie.value = this.vipStatus;

        return response.data;
      } catch (error) {
        this.logout();
        throw new Error(error.response?.data?.message || 'Registration failed');
      }
    },

    async logout() {
      const { $api } = useNuxtApp();
      try {
        if (this.isAuthenticated) {
          await $api.post('/v1/logout');
        }
      } catch (error) {
        console.error('Logout API call failed:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.vipStatus = null;
        this.isAuthenticated = false;

        const tokenCookie = useCookie('sanctum_token', { path: '/' });
        const userCookie = useCookie('user', { path: '/' });
        const vipStatusCookie = useCookie('vip_status', { path: '/' });
        tokenCookie.value = null;
        userCookie.value = null;
        vipStatusCookie.value = null;
      }
    },

    setUser(user) {
      this.user = user;
      const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
      userCookie.value = user;
    },

    async fetchMe() {
      const { $api } = useNuxtApp();
      if (!this.token) return null;

      const response = await $api.get('/v1/me');
      this.setUser(response.data.user);
      this.setVipStatus(response.data.vip_status);
      return response.data.user;
    },
  },
});
