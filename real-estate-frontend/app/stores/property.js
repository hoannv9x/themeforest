import { defineStore } from 'pinia';
import { useNuxtApp } from '#app';

export const usePropertyStore = defineStore('property', {
  state: () => ({
    properties: [],
    property: null, // For single property details
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0,
      perPage: 15,
    },
    filters: {},
    sortBy: { column: 'published_at', direction: 'desc' },
    loading: false,
    error: null,
  }),

  getters: {
    getAllProperties: (state) => state.properties,
    getSingleProperty: (state) => state.property,
    getPagination: (state) => state.pagination,
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },

  actions: {
    setFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters };
      this.pagination.currentPage = 1; // Reset page on filter change
    },

    setSortBy(column, direction) {
      this.sortBy = { column, direction };
      this.pagination.currentPage = 1; // Reset page on sort change
    },

    setCurrentPage(page) {
      this.pagination.currentPage = page;
    },

    async fetchProperties() {
      this.loading = true;
      this.error = null;
      const { $api } = useNuxtApp();
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          sort_by: this.sortBy.column,
          sort_direction: this.sortBy.direction,
          ...this.filters,
        };
        const response = await $api.get('/properties', { params });
        this.properties = response.data.data;
        this.pagination.currentPage = response.data.meta.current_page;
        this.pagination.lastPage = response.data.meta.last_page;
        this.pagination.total = response.data.meta.total;
        this.pagination.perPage = response.data.meta.per_page;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch properties.';
        console.error('Error fetching properties:', error);
      } finally {
        this.loading = false;
      }
    },

    async fetchProperty(id) {
      this.loading = true;
      this.error = null;
      const { $api } = useNuxtApp();
      try {
        const response = await $api.get(`/properties/${id}`);
        this.property = response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch property details.';
        console.error('Error fetching property:', error);
      } finally {
        this.loading = false;
      }
    },

    async createProperty(propertyData) {
      this.loading = true;
      this.error = null;
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post('/properties', propertyData);
        // Optionally, add the new property to the list or refresh
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create property.';
        console.error('Error creating property:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateProperty(id, propertyData) {
      this.loading = true;
      this.error = null;
      const { $api } = useNuxtApp();
      try {
        const response = await $api.post(`/properties/${id}`, propertyData); // Use POST with _method for file uploads
        // Optionally, update the property in the list or refresh
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update property.';
        console.error('Error updating property:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteProperty(id) {
      this.loading = true;
      this.error = null;
      const { $api } = useNuxtApp();
      try {
        await $api.delete(`/properties/${id}`);
        this.properties = this.properties.filter(p => p.id !== id);
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete property.';
        console.error('Error deleting property:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});