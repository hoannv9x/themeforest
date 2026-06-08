<template>
  <div
    class="flex min-h-[calc(100vh-200px)] flex-col justify-center py-12 sm:px-6 lg:px-8 relative"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="text-center space-y-2 mb-8">
        <div
          class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 mb-4"
        >
          <UserPlus class="w-6 h-6 text-accent" />
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-gradient">Create Account</h2>
        <p class="text-foreground-muted">Join our community and start your journey</p>
      </div>

      <div class="card-glass p-8 md:p-10">
        <form class="space-y-6" @submit.prevent="handleRegister">
          <div
            v-if="auth.error"
            class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-500 text-sm flex items-center gap-3"
          >
            <AlertCircle class="w-4 h-4" />
            {{ auth.error }}
          </div>

          <div class="space-y-2">
            <label for="name" class="text-sm font-medium text-foreground-muted ml-1"
              >Full Name</label
            >
            <div class="relative group">
              <User
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors"
              />
              <input
                v-model="form.name"
                id="name"
                type="text"
                placeholder="John Doe"
                required
                class="input-field !pl-10"
              />
            </div>
            <p v-if="auth.validationErrors.name" class="mt-1 text-xs text-red-500">
              {{ auth.validationErrors.name[0] }}
            </p>
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
            <p v-if="auth.validationErrors.email" class="mt-1 text-xs text-red-500">
              {{ auth.validationErrors.email[0] }}
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label for="password" class="text-sm font-medium text-foreground-muted ml-1"
                >Password</label
              >
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
            </div>
            <div class="space-y-2">
              <label
                for="password_confirmation"
                class="text-sm font-medium text-foreground-muted ml-1"
                >Confirm</label
              >
              <div class="relative group">
                <Lock
                  class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors"
                />
                <input
                  v-model="form.password_confirmation"
                  id="password_confirmation"
                  type="password"
                  placeholder="••••••••"
                  required
                  class="input-field !pl-10"
                />
              </div>
            </div>
          </div>
          <p v-if="auth.validationErrors.password" class="mt-1 text-xs text-red-500">
            {{ auth.validationErrors.password[0] }}
          </p>

          <button type="submit" :disabled="auth.loading" class="btn-primary w-full">
            <template v-if="auth.loading">
              <Loader2 class="w-4 h-4 animate-spin" />
              Creating account...
            </template>
            <template v-else> Create Account </template>
          </button>
        </form>

        <div class="mt-8 pt-8 border-t border-border-default text-center">
          <p class="text-sm text-foreground-muted">
            Already have an account?
            <NuxtLink
              to="/login"
              class="font-semibold text-accent hover:text-accent-bright transition-colors"
              >Sign in</NuxtLink
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
import { UserPlus, User, Mail, Lock, AlertCircle, Loader2 } from "lucide-vue-next";

definePageMeta({
  middleware: 'guest'
})

const auth = useAuthStore();
const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const handleRegister = async () => {
  const success = await auth.register(
    form.name,
    form.email,
    form.password,
    form.password_confirmation
  );
  if (success) {
    navigateTo("/dashboard");
  }
};
</script>
