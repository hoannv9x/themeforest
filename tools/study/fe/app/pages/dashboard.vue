<template>
  <div class="space-y-10 pb-20">
    <!-- Hero Greeting -->
    <section class="card-glass p-8 md:p-12 relative overflow-hidden group">
      <div class="absolute inset-0 bg-accent/5 pointer-events-none group-hover:bg-accent/10 transition-colors"></div>
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div class="space-y-2">
          <div class="label-mono mb-2">Welcome Back</div>
          <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gradient">
            Hello, {{ auth.user?.name }}!
          </h1>
          <p class="text-foreground-muted text-lg max-w-md">
            Ready to continue your English journey today? You're doing great!
          </p>
        </div>
        <div class="flex gap-4">
          <div class="px-6 py-4 rounded-2xl bg-bg-base/50 border border-border-default text-center min-w-[120px]">
            <div class="text-2xl font-bold text-accent">12</div>
            <div class="text-xs text-foreground-subtle font-mono uppercase tracking-widest mt-1">Day Streak</div>
          </div>
          <div class="px-6 py-4 rounded-2xl bg-bg-base/50 border border-border-default text-center min-w-[120px]">
            <div class="text-2xl font-bold text-indigo-500">2.4k</div>
            <div class="text-xs text-foreground-subtle font-mono uppercase tracking-widest mt-1">Total XP</div>
          </div>
        </div>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Profile Sidebar -->
      <aside class="space-y-6">
        <div class="card-glass p-6 space-y-6">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-accent/10 border-2 border-accent/20 flex items-center justify-center relative">
              <User class="w-8 h-8 text-accent" />
              <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-4 border-bg-elevated rounded-full"></div>
            </div>
            <div>
              <h3 class="font-bold text-lg">{{ auth.user?.name }}</h3>
              <p class="text-sm text-foreground-muted">{{ auth.user?.email }}</p>
            </div>
          </div>

          <div class="space-y-4 pt-4 border-t border-border-default">
            <div class="flex justify-between items-center">
              <span class="text-sm text-foreground-muted">Level</span>
              <span class="px-2 py-1 rounded-md bg-accent/10 text-accent text-xs font-bold uppercase tracking-wider">
                {{ auth.user?.profile?.level || 'Beginner' }}
              </span>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between text-xs font-mono uppercase tracking-widest text-foreground-subtle">
                <span>Progress to Level 5</span>
                <span>75%</span>
              </div>
              <div class="h-2 w-full bg-bg-base rounded-full border border-border-default overflow-hidden">
                <div class="h-full bg-accent rounded-full w-[75%] shadow-accent-glow"></div>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <span class="text-sm text-foreground-muted">Your Goal</span>
            <div class="p-3 rounded-xl bg-bg-base/50 border border-border-default italic text-sm text-foreground-muted">
              "{{ auth.user?.profile?.goal || 'No goal set yet. Set a goal to stay focused!' }}"
            </div>
          </div>

          <NuxtLink to="/profile" class="btn-secondary w-full !py-2 text-sm">
            <Settings class="w-4 h-4" />
            Account Settings
          </NuxtLink>
        </div>

        <div class="card-glass p-6 space-y-4">
          <h3 class="font-bold flex items-center gap-2">
            <Trophy class="w-5 h-5 text-yellow-500" />
            Achievements
          </h3>
          <div class="flex flex-wrap gap-2">
            <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-lg bg-bg-base border border-border-default flex items-center justify-center group cursor-help transition-all hover:border-accent" :title="`Achievement ${i}`">
              <Award class="w-5 h-5 text-foreground-subtle group-hover:text-accent transition-colors" />
            </div>
            <div class="w-10 h-10 rounded-lg bg-bg-base/30 border border-dashed border-border-default flex items-center justify-center text-foreground-subtle text-xs">
              +5
            </div>
          </div>
        </div>
      </aside>

      <!-- Main Content: Courses -->
      <div class="lg:col-span-2 space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-bold tracking-tight">Active Courses</h2>
          <NuxtLink to="/courses" class="text-sm font-medium text-accent hover:text-accent-bright flex items-center gap-1 transition-colors">
            Browse All <ArrowRight class="w-4 h-4" />
          </NuxtLink>
        </div>
        
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 card-glass space-y-4">
          <Loader2 class="w-8 h-8 text-accent animate-spin" />
          <p class="text-foreground-muted font-mono uppercase tracking-widest text-xs">Loading courses...</p>
        </div>
        
        <div v-else-if="myCourses.length === 0" class="flex flex-col items-center justify-center py-20 card-glass text-center px-6">
          <div class="w-16 h-16 rounded-full bg-bg-base border border-border-default flex items-center justify-center mb-4">
            <BookOpen class="w-8 h-8 text-foreground-subtle" />
          </div>
          <p class="text-foreground-muted mb-6 max-w-sm">You haven't started any courses yet. Start your journey today with our curated content.</p>
          <NuxtLink to="/courses" class="btn-primary">
            Explore Courses
          </NuxtLink>
        </div>
        
        <div v-else class="grid grid-cols-1 gap-4">
          <div v-for="course in myCourses" :key="course.id" class="card-glass p-6 group hover:translate-x-1 transition-all">
            <div class="flex flex-col sm:flex-row items-center gap-6">
              <div class="relative w-24 h-24 flex-shrink-0">
                <img :src="course.thumbnail" class="w-full h-full object-cover rounded-xl shadow-lg transition-transform group-hover:scale-105" />
                <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-xl"></div>
              </div>
              <div class="flex-1 min-w-0 space-y-2">
                <div class="flex items-center justify-between">
                  <h4 class="font-bold text-lg truncate group-hover:text-accent transition-colors">{{ course.title }}</h4>
                  <span class="text-xs font-mono text-foreground-subtle">60% Complete</span>
                </div>
                <p class="text-sm text-foreground-muted line-clamp-1">{{ course.description }}</p>
                <div class="h-1.5 w-full bg-bg-base rounded-full border border-border-default overflow-hidden mt-2">
                  <div class="h-full bg-accent rounded-full w-[60%]"></div>
                </div>
              </div>
              <NuxtLink :to="`/courses/${course.slug}`" class="btn-secondary !py-2 !px-4 !text-xs whitespace-nowrap group-hover:bg-accent group-hover:text-white transition-all">
                Continue Learning
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
import axios from 'axios'
import { 
  User, 
  Settings, 
  BookOpen, 
  ArrowRight, 
  Loader2, 
  Trophy, 
  Award,
  Zap,
  LayoutDashboard
} from 'lucide-vue-next'

const auth = useAuthStore()
const config = useRuntimeConfig()
const myCourses = ref([])
const loading = ref(true)

definePageMeta({
  middleware: 'auth'
})

onMounted(async () => {
  try {
    const response = await axios.get(`${config.public.apiBase}/my-courses`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    myCourses.value = response.data.data
  } catch (err) {
    console.error('Failed to fetch courses', err)
  } finally {
    loading.value = false
  }
})
</script>
