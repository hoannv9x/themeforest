// middleware/auth.ts
import { useAuthStore } from '~/stores/auth';

export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  // If the user is not authenticated and tries to access a protected route, redirect to login
  if (!authStore.isAuthenticated && to.path.startsWith('/dashboard')) {
    return navigateTo('/login', { replace: true });
  }

  // If the user is authenticated and tries to access login/register, redirect to dashboard
  if (authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/dashboard', { replace: true });
  }
});