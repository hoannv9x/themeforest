import { useAuthStore } from "~/stores/auth";

export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useAuthStore();

  if (auth.isLoggedIn) {
    return navigateTo("/dashboard");
  }
});
