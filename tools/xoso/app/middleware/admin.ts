import { useAuthStore } from '~/stores/auth';

export default defineNuxtRouteMiddleware(async () => {
  const auth = useAuthStore();
  auth.initAuth();

  if (!auth.getIsAuthenticated) {
    return navigateTo('/login');
  }

  if (!auth.getUser) {
    try {
      await auth.fetchMe();
    } catch (e) {
      await auth.logout();
      return navigateTo('/login');
    }
  }

  const user = auth.getUser;
  const isAdmin = user?.role === 'admin';
  const isDeveloper = user?.permission === 'developer';
  if (!isAdmin && !isDeveloper) {
    return navigateTo('/dashboard');
  }
});
