import { defineStore } from 'pinia';
import { useNuxtApp, useCookie } from '#app';

export const useAuthStore = defineStore('auth', {
  state: () => {
    // Regular User
    const userToken = useCookie('sanctum_token');
    const userData = useCookie('user');

    return {
      // Regular User state
      user: userData.value || null,
      token: userToken.value || null,
      isAuthenticated: !!userToken.value,
    }
  },

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    getIsAuthenticated: (state) => state.isAuthenticated,
  },

  actions: {
    // Initialize auth state (can be used to re-sync cookies)
    initAuth() {
      // Cookies are already read in state(), but we can ensure reactivity here if needed
      const userToken = useCookie('sanctum_token');
      const userData = useCookie('user');

      this.token = userToken.value || null;
      this.user = userData.value || null;
      this.isAuthenticated = !!userToken.value;
    },

    // User login
    async login(email, password) {
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/v1/login', { email, password });
        this.token = response.data.token;
        this.user = response.data.user;
        this.isAuthenticated = true;

        const tokenCookie = useCookie('sanctum_token', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });

        tokenCookie.value = this.token;
        userCookie.value = this.user;

        return response.data;
      } catch (error) {
        this.logout();
        throw new Error(error.response?.data?.message || 'Login failed');
      }
    },

    async register(name, email, password, password_confirmation) {
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/v1/register', { name, email, password, password_confirmation });
        this.token = response.data.token;
        this.user = response.data.user;
        this.isAuthenticated = true;

        const tokenCookie = useCookie('sanctum_token', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });

        tokenCookie.value = this.token;
        userCookie.value = this.user;

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
        this.isAuthenticated = false;

        const tokenCookie = useCookie('sanctum_token', { path: '/' });
        const userCookie = useCookie('user', { path: '/' });
        tokenCookie.value = null;
        userCookie.value = null;
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
      return response.data.user;
    },
  },
});
