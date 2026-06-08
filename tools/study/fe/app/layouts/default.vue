<template>
  <div :class="{ dark: isDark }" class="min-h-screen font-sans">
    <div
      class="min-h-screen bg-bg-base text-foreground transition-colors duration-500 relative overflow-hidden"
    >
      <!-- Background System -->
      <div class="absolute inset-0 z-0 pointer-events-none">
        <!-- Layer 1: Base Radial Gradient -->
        <div
          class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,var(--bg-elevated)_0%,var(--bg-base)_50%,var(--bg-deep)_100%)]"
        ></div>

        <!-- Layer 2: Grid Overlay -->
        <div class="absolute inset-0 bg-grid opacity-[0.03] dark:opacity-[0.02]"></div>

        <!-- Layer 3: Noise Texture -->
        <div
          class="absolute inset-0 bg-noise opacity-[0.015] dark:opacity-[0.02] mix-blend-overlay"
        ></div>

        <!-- Layer 4: Animated Blobs -->
        <div
          class="absolute top-[-10%] left-[20%] w-[600px] h-[600px] bg-accent/20 blur-[120px] rounded-full animate-float mix-blend-multiply dark:mix-blend-soft-light"
        ></div>
        <div
          class="absolute bottom-[-10%] right-[10%] w-[500px] h-[500px] bg-indigo-500/10 blur-[100px] rounded-full animate-float mix-blend-multiply dark:mix-blend-soft-light"
          style="animation-delay: -5s"
        ></div>
      </div>

      <!-- Header -->
      <header
        class="sticky top-0 z-50 w-full border-b border-border-default bg-bg-base/80 backdrop-blur-xl"
      >
        <nav
          class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
          <div class="flex items-center gap-8">
            <NuxtLink to="/" class="flex items-center gap-2 group">
              <div
                class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center shadow-accent-glow transition-transform group-hover:scale-110"
              >
                <BookOpen class="w-5 h-5 text-white" />
              </div>
              <span class="text-xl font-bold tracking-tight text-gradient"
                >EnglishStudy</span
              >
            </NuxtLink>

            <div class="hidden md:flex items-center gap-1">
              <NuxtLink
                to="/courses"
                class="px-4 py-2 text-sm font-medium text-foreground-muted hover:text-foreground transition-colors rounded-lg hover:bg-surface"
              >
                All Courses
              </NuxtLink>
              <NuxtLink
                to="/vocabulary"
                class="px-4 py-2 text-sm font-medium text-foreground-muted hover:text-foreground transition-colors rounded-lg hover:bg-surface"
              >
                Vocabulary
              </NuxtLink>
              <NuxtLink
                v-if="auth.isLoggedIn"
                to="/dashboard"
                class="px-4 py-2 text-sm font-medium text-foreground-muted hover:text-foreground transition-colors rounded-lg hover:bg-surface"
              >
                Dashboard
              </NuxtLink>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <!-- Theme Toggle -->
            <button
              @click="toggleTheme"
              class="p-2 rounded-lg bg-surface border border-border-default hover:border-border-hover transition-all text-foreground-muted hover:text-foreground"
            >
              <Sun v-if="isDark" class="w-5 h-5" />
              <Moon v-else class="w-5 h-5" />
            </button>

            <div class="h-6 w-px bg-border-default mx-1 hidden sm:block"></div>

            <template v-if="auth.isLoggedIn">
              <NuxtLink
                to="/profile"
                class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-surface transition-colors group"
              >
                <div
                  class="w-8 h-8 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center"
                >
                  <User class="w-4 h-4 text-accent" />
                </div>
                <span
                  class="text-sm font-medium text-foreground-muted group-hover:text-foreground"
                  >{{ auth.user?.name }}</span
                >
              </NuxtLink>
              <button @click="auth.logout" class="btn-secondary !py-2 !px-4 !text-sm">
                Logout
              </button>
            </template>
            <template v-else>
              <NuxtLink
                to="/login"
                class="px-4 py-2 text-sm font-medium text-foreground-muted hover:text-foreground transition-colors"
              >
                Login
              </NuxtLink>
              <NuxtLink to="/register" class="btn-primary !py-2 !px-4 !text-sm">
                Get Started
              </NuxtLink>
            </template>

            <!-- Mobile Menu Toggle -->
            <button
              @click="isMenuOpen = !isMenuOpen"
              class="md:hidden p-2 rounded-lg bg-surface border border-border-default text-foreground-muted"
            >
              <Menu v-if="!isMenuOpen" class="w-5 h-5" />
              <X v-else class="w-5 h-5" />
            </button>
          </div>
        </nav>

        <!-- Mobile Menu -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 -translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-4"
        >
          <div
            v-if="isMenuOpen"
            class="md:hidden border-b border-border-default bg-bg-base/95 backdrop-blur-xl absolute w-full"
          >
            <div class="px-4 py-6 space-y-4">
              <NuxtLink
                to="/courses"
                class="block text-lg font-medium text-foreground-muted hover:text-foreground px-4 py-2 rounded-xl hover:bg-surface"
                >All Courses</NuxtLink
              >
              <NuxtLink
                to="/vocabulary"
                class="block text-lg font-medium text-foreground-muted hover:text-foreground px-4 py-2 rounded-xl hover:bg-surface"
                >Vocabulary</NuxtLink
              >
              <NuxtLink
                v-if="auth.isLoggedIn"
                to="/dashboard"
                class="block text-lg font-medium text-foreground-muted hover:text-foreground px-4 py-2 rounded-xl hover:bg-surface"
                >Dashboard</NuxtLink
              >
              <div class="h-px bg-border-default mx-4"></div>
              <template v-if="auth.isLoggedIn">
                <NuxtLink
                  to="/profile"
                  class="block text-lg font-medium text-foreground-muted hover:text-foreground px-4 py-2 rounded-xl hover:bg-surface"
                  >Profile</NuxtLink
                >
                <button @click="auth.logout" class="w-full btn-secondary">Logout</button>
              </template>
              <template v-else>
                <NuxtLink
                  to="/login"
                  class="block text-lg font-medium text-foreground-muted hover:text-foreground px-4 py-2 rounded-xl hover:bg-surface"
                  >Login</NuxtLink
                >
                <NuxtLink to="/register" class="w-full btn-primary">Get Started</NuxtLink>
              </template>
            </div>
          </div>
        </transition>
      </header>

      <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <slot />
      </main>

      <footer class="relative z-10 border-t border-border-default bg-bg-deep/50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col md:flex-row justify-between items-center gap-8">
            <NuxtLink to="/" class="flex items-center gap-2 group">
              <div class="w-6 h-6 bg-accent/20 rounded flex items-center justify-center">
                <BookOpen class="w-4 h-4 text-accent" />
              </div>
              <span class="text-lg font-bold tracking-tight text-gradient"
                >EnglishStudy</span
              >
            </NuxtLink>

            <div class="flex gap-8 text-sm text-foreground-muted">
              <a href="#" class="hover:text-foreground transition-colors"
                >Privacy Policy</a
              >
              <a href="#" class="hover:text-foreground transition-colors"
                >Terms of Service</a
              >
              <a href="#" class="hover:text-foreground transition-colors">Contact</a>
            </div>

            <div class="text-sm text-foreground-subtle">
              &copy; {{ new Date().getFullYear() }} English Study Platform.
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "~/stores/auth";
import {
  BookOpen,
  Sun,
  Moon,
  Menu,
  X,
  User,
  LogOut,
  LayoutDashboard,
  GraduationCap,
} from "lucide-vue-next";

const auth = useAuthStore();
const isDark = ref(true);
const isMenuOpen = ref(false);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (process.client) {
    localStorage.setItem("theme", isDark.value ? "dark" : "light");
  }
};

onMounted(() => {
  auth.initialize();

  if (process.client) {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
      isDark.value = savedTheme === "dark";
    } else {
      isDark.value = window.matchMedia("(prefers-color-scheme: dark)").matches;
    }
  }
});

// Close menu on navigation
const router = useRouter();
router.afterEach(() => {
  isMenuOpen.value = false;
});
</script>
