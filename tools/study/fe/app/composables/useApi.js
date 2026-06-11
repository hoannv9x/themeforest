import axios from "axios";
import { useAuthStore } from "~/stores/auth";

export const useApi = () => {
  const config = useRuntimeConfig();
  const auth = useAuthStore();

  const api = axios.create({
    baseURL: config.public.apiBase,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  });

  // Add interceptor to inject token
  api.interceptors.request.use((config) => {
    if (auth.token) {
      config.headers.Authorization = `Bearer ${auth.token}`;
    }
    return config;
  });

  // Add interceptor to handle errors globally
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      // Avoid infinite loop if auth requests fail with 401
      const isAuthRequest = ["/login", "/logout", "/register"].some((path) =>
        error.config?.url?.includes(path)
      );

      if (error.response?.status === 401 && !isAuthRequest) {
        auth.logout();
      }
      return Promise.reject(error);
    }
  );

  return api;
};
