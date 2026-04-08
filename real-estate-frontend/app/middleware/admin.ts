import { useAuthStore } from '~/stores/auth';

export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();
  
  // Redirect to admin login if admin is not authenticated
  if (!authStore.isAdminAuthenticated) {
    return navigateTo('/admin/login');
  }
  
  // Check if the user is actually an admin
  if (!authStore.adminUser || !authStore.adminUser.is_admin) {
    return navigateTo('/admin/login');
  }
});