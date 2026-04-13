import axios from 'axios';

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  const api = axios.create({
    baseURL: config.public.apiBaseUrl, // Your Laravel API base URL
    headers: {
      common: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    },
    withCredentials: true, // Important for Sanctum SPA authentication
  });

  // Optional: Add an interceptor to attach the Sanctum token
  api.interceptors.request.use((request) => {
    // Determine which token to use based on the current path
    // If we are on an admin route, use the admin token
    // Otherwise, use the regular user token
    const url = useRequestURL();
    const isAdminPath = url.pathname.startsWith('/admin');

    let token = null;
    if (isAdminPath) {
      token = useCookie('admin_token').value;
    } else {
      token = useCookie('sanctum_token').value;
    }

    if (token) {
      request.headers.Authorization = `Bearer ${token}`;
    }
    return request;
  });

  // Optional: Add an interceptor for error handling (e.g., redirect on 401)
  api.interceptors.response.use(
    (response) => {
      return response;
    },
    (error) => {
      if (error.response && error.response.status === 401) {
        // Handle unauthorized access, e.g., redirect to login
        // nuxtApp.$router.push('/login');
      }
      return Promise.reject(error);
    }
  );

  return {
    provide: {
      api: api,
    },
  };
});