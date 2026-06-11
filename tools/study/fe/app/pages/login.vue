<template>
  <div
    class="flex min-h-[calc(100vh-200px)] flex-col justify-center py-12 sm:px-6 lg:px-8 relative"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="text-center space-y-2 mb-8">
        <div
          class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 mb-4"
        >
          <LogIn class="w-6 h-6 text-accent" />
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-gradient">Welcome Back</h2>
        <p class="text-foreground-muted">Enter your credentials to access your account</p>
      </div>

      <div class="card-glass p-8 md:p-10">
        <form class="space-y-6" @submit.prevent="handleLogin">
          <div
            v-if="auth.error"
            class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-500 text-sm flex items-center gap-3"
          >
            <AlertCircle class="w-4 h-4" />
            {{ auth.error }}
          </div>

          <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-foreground-muted ml-1"
              >Email address</label
            >
            <div class="relative group">
              <Mail
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors"
              />
              <input
                v-model="form.email"
                id="email"
                type="email"
                placeholder="name@example.com"
                required
                class="input-field !pl-10"
              />
            </div>
            <p v-if="auth.validationErrors?.email" class="mt-1 text-xs text-red-500">
              {{ auth.validationErrors.email[0] }}
            </p>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between ml-1">
              <label for="password" class="text-sm font-medium text-foreground-muted"
                >Password</label
              >
              <a
                href="#"
                class="text-xs font-medium text-accent hover:text-accent-bright transition-colors"
                >Forgot password?</a
              >
            </div>
            <div class="relative group">
              <Lock
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors"
              />
              <input
                v-model="form.password"
                id="password"
                type="password"
                placeholder="••••••••"
                required
                class="input-field !pl-10"
              />
            </div>
            <p v-if="auth.validationErrors?.password" class="mt-1 text-xs text-red-500">
              {{ auth.validationErrors.password[0] }}
            </p>
          </div>

          <button type="submit" :disabled="auth.loading" class="btn-primary w-full">
            <template v-if="auth.loading">
              <Loader2 class="w-4 h-4 animate-spin" />
              Signing in...
            </template>
            <template v-else> Sign in </template>
          </button>
        </form>

        <div class="mt-8 pt-8 border-t border-border-default text-center">
          <p class="text-sm text-foreground-muted">
            Don't have an account?
            <NuxtLink
              to="/register"
              class="font-semibold text-accent hover:text-accent-bright transition-colors"
              >Create one now</NuxtLink
            >
          </p>
        </div>
      </div>
    </div>

    <!-- Background Decoration -->
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-accent/5 blur-[120px] rounded-full -z-0"
    ></div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";
import { LogIn, Mail, Lock, AlertCircle, Loader2 } from "lucide-vue-next";

definePageMeta({
  middleware: "guest",
});

const auth = useAuthStore();
const form = reactive({
  email: "",
  password: "",
});

const handleLogin = async () => {
  const success = await auth.login(form.email, form.password);
  if (success) {
    navigateTo("/dashboard");
  }
};
</script>
