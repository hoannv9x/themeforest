import { defineStore } from 'pinia';
import { useNuxtApp, useCookie } from '#app';

export const useAuthStore = defineStore('auth', {
  state: () => {
    // Regular User
    const userToken = useCookie('sanctum_token');
    const userData = useCookie('user');

    // Admin User
    const adminToken = useCookie('admin_token');
    const adminData = useCookie('admin_user');

    return {
      // Regular User state
      user: userData.value || null,
      token: userToken.value || null,
      isAuthenticated: !!userToken.value,

      // Admin User state
      adminUser: adminData.value || null,
      adminToken: adminToken.value || null,
      isAdminAuthenticated: !!adminToken.value,
    }
  },

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    getIsAuthenticated: (state) => state.isAuthenticated,

    getAdminUser: (state) => state.adminUser,
    getAdminToken: (state) => state.adminToken,
    getIsAdminAuthenticated: (state) => state.isAdminAuthenticated,
  },

  actions: {
    // Initialize auth state (can be used to re-sync cookies)
    initAuth() {
      // Cookies are already read in state(), but we can ensure reactivity here if needed
      const userToken = useCookie('sanctum_token');
      const userData = useCookie('user');
      const adminToken = useCookie('admin_token');
      const adminData = useCookie('admin_user');

      this.token = userToken.value || null;
      this.user = userData.value || null;
      this.isAuthenticated = !!userToken.value;

      this.adminToken = adminToken.value || null;
      this.adminUser = adminData.value || null;
      this.isAdminAuthenticated = !!adminToken.value;
    },

    // User login
    async login(email, password) {
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/login', { email, password });
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

    // Admin login
    async adminLogin(email, password) {
      const { $api } = useNuxtApp();
      try {
        // Assuming the same endpoint but checking admin status on return
        const response = await $api.post('/login', { email, password });
        
        if (!response.data.user.is_admin) {
            throw new Error('Unauthorized. Not an admin.');
        }

        this.adminToken = response.data.token;
        this.adminUser = response.data.user;
        this.isAdminAuthenticated = true;

        const tokenCookie = useCookie('admin_token', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        const userCookie = useCookie('admin_user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
        
        tokenCookie.value = this.adminToken;
        userCookie.value = this.adminUser;

        return response.data;
      } catch (error) {
        this.adminLogout();
        throw new Error(error.message || error.response?.data?.message || 'Admin Login failed');
      }
    },

    async register(name, email, password, password_confirmation) {
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/register', { name, email, password, password_confirmation });
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
          // Pass the specific token for the logout request if needed, 
          // but axios interceptor handles it based on path normally.
          await $api.post('/logout');
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

    async adminLogout() {
      const { $api } = useNuxtApp();
      try {
        if (this.isAdminAuthenticated) {
          await $api.post('/logout');
        }
      } catch (error) {
        console.error('Admin Logout API call failed:', error);
      } finally {
        this.adminUser = null;
        this.adminToken = null;
        this.isAdminAuthenticated = false;
        
        const tokenCookie = useCookie('admin_token', { path: '/' });
        const userCookie = useCookie('admin_user', { path: '/' });
        tokenCookie.value = null;
        userCookie.value = null;
      }
    },

    setUser(user) {
      this.user = user;
      const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
      userCookie.value = user;
    },

    setAdminUser(user) {
      this.adminUser = user;
      const userCookie = useCookie('admin_user', { maxAge: 60 * 60 * 24 * 7, path: '/' });
      userCookie.value = user;
    },
  },
});
