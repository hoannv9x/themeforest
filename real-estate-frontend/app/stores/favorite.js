import { defineStore } from 'pinia';
import { useNuxtApp } from '#app';
import { useAuthStore } from './auth'; // Import auth store

export const useFavoriteStore = defineStore('favorite', {
  state: () => ({
    favorites: [],
    loading: false,
    error: null,
  }),

  getters: {
    getFavorites: (state) => state.favorites,
    isPropertyFavorited: (state) => (propertyId) => {
      return state.favorites.some(fav => fav.property_id === propertyId);
    },
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },

  actions: {
    async fetchFavorites() {
      this.loading = true;
      this.error = null;
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.loading = false;
        return;
      }

      const { $api } = useNuxtApp();
      try {
        const response = await $api.get('/favorites');
        this.favorites = response.data.data; // Assuming API returns data.data for favorites
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch favorites.';
        console.error('Error fetching favorites:', error);
      } finally {
        this.loading = false;
      }
    },

    async addFavorite(propertyId) {
      this.loading = true;
      this.error = null;
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.loading = false;
        throw new Error('User not authenticated.');
      }

      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/favorites', { property_id: propertyId });
        this.favorites.push(response.data.favorite); // Assuming API returns the new favorite object
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add to favorites.';
        console.error('Error adding favorite:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeFavorite(propertyId) {
      this.loading = true;
      this.error = null;
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.loading = false;
        throw new Error('User not authenticated.');
      }

      const { $api } = useNuxtApp();
      try {
        await $api.delete(`/favorites/${propertyId}`);
        this.favorites = this.favorites.filter(fav => fav.property_id !== propertyId);
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove from favorites.';
        console.error('Error removing favorite:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});