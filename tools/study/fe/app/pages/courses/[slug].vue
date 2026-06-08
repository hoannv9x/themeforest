<template>
  <div class="space-y-12 pb-20">
    <div v-if="loading" class="flex flex-col items-center justify-center py-40 space-y-4">
      <Loader2 class="w-12 h-12 text-accent animate-spin" />
      <p class="text-foreground-muted font-mono uppercase tracking-widest text-xs">Loading course details...</p>
    </div>
    
    <div v-else-if="course" class="space-y-12">
      <!-- Hero Header -->
      <section class="relative rounded-3xl overflow-hidden">
        <div class="absolute inset-0 bg-bg-elevated/80 backdrop-blur-xl z-0"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-accent/20 to-transparent z-0"></div>
        
        <div class="relative z-10 p-8 md:p-16 flex flex-col md:flex-row gap-12 items-center">
          <div class="relative w-full md:w-80 aspect-[4/3] flex-shrink-0 group">
            <img :src="course.thumbnail" class="w-full h-full object-cover rounded-2xl shadow-2xl transition-transform duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 ring-1 ring-inset ring-white/20 rounded-2xl"></div>
          </div>
          
          <div class="space-y-6 flex-1 text-center md:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/20 border border-accent/30">
              <span class="label-mono !text-[10px] text-white">Course Details</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-gradient leading-tight">
              {{ course.title }}
            </h1>
            <p class="text-xl text-foreground-muted max-w-2xl leading-relaxed">
              {{ course.description }}
            </p>
            
            <div class="flex flex-wrap gap-4 justify-center md:justify-start pt-4">
              <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-bg-base/50 border border-border-default">
                <Users class="w-4 h-4 text-accent" />
                <span class="text-sm font-medium">1,240 Enrolled</span>
              </div>
              <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-bg-base/50 border border-border-default">
                <Clock class="w-4 h-4 text-accent" />
                <span class="text-sm font-medium">12.5 Hours Content</span>
              </div>
              <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-bg-base/50 border border-border-default">
                <Star class="w-4 h-4 text-yellow-500" />
                <span class="text-sm font-medium">4.9 (210 Reviews)</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Content List -->
        <div class="lg:col-span-2 space-y-8">
          <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold tracking-tight">Curriculum</h2>
            <span class="text-sm text-foreground-muted">{{ course.modules?.length || 0 }} Modules &bull; {{ totalLessonsCount }} Lessons</span>
          </div>
          
          <div class="space-y-4">
            <div v-for="module in course.modules" :key="module.id" class="card-glass overflow-hidden">
              <div class="px-6 py-4 bg-bg-elevated/50 border-b border-border-default flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-3">
                  <span class="w-6 h-6 rounded-md bg-accent/10 flex items-center justify-center text-[10px] text-accent font-mono border border-accent/20">
                    {{ module.sort_order }}
                  </span>
                  {{ module.title }}
                </h3>
              </div>
              <ul class="divide-y divide-border-default">
                <li v-for="lesson in module.lessons" :key="lesson.id" 
                  class="px-6 py-4 flex items-center justify-between group hover:bg-surface transition-colors cursor-pointer"
                  @click="navigateTo(auth.isLoggedIn ? `/lessons/${lesson.slug}` : '/login')">
                  <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-bg-base border border-border-default flex items-center justify-center text-foreground-subtle group-hover:text-accent group-hover:border-accent transition-all">
                      <Play v-if="lesson.content_type === 'video'" class="w-4 h-4" />
                      <FileText v-else class="w-4 h-4" />
                    </div>
                    <span class="text-sm font-medium text-foreground-muted group-hover:text-foreground transition-colors">
                      {{ lesson.title }}
                    </span>
                  </div>
                  <div v-if="auth.isLoggedIn && lesson.is_completed" class="w-6 h-6 rounded-full bg-green-500/10 flex items-center justify-center border border-green-500/20">
                    <Check class="w-3 h-3 text-green-500" />
                  </div>
                  <ChevronRight v-else class="w-4 h-4 text-foreground-subtle opacity-0 group-hover:opacity-100 transition-opacity" />
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Sidebar Progress/Enroll -->
        <aside class="space-y-6">
          <div class="card-glass p-8 sticky top-24 space-y-8">
            <div class="space-y-4">
              <h3 class="text-xl font-bold">Your Progress</h3>
              <div v-if="auth.isLoggedIn" class="space-y-4">
                <div class="flex justify-between items-end">
                  <div class="text-3xl font-bold text-accent">{{ progressPercentage }}%</div>
                  <div class="text-xs font-mono text-foreground-subtle mb-1">
                    {{ completedLessonsCount }}/{{ totalLessonsCount }} Lessons
                  </div>
                </div>
                <div class="h-3 w-full bg-bg-base rounded-full border border-border-default overflow-hidden">
                  <div class="h-full bg-accent rounded-full shadow-accent-glow transition-all duration-1000" :style="{ width: progressPercentage + '%' }"></div>
                </div>
                <button @click="navigateTo(nextLessonUrl)" class="btn-primary w-full group">
                  Continue Learning
                  <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                </button>
              </div>
              <div v-else class="space-y-4 text-center">
                <div class="p-6 rounded-2xl bg-bg-base/50 border border-dashed border-border-default mb-4">
                  <p class="text-sm text-foreground-muted">Join now to start learning and track your progress.</p>
                </div>
                <NuxtLink to="/login" class="btn-primary w-full">Enroll Now</NuxtLink>
                <p class="text-xs text-foreground-subtle">No credit card required for free trials</p>
              </div>
            </div>

            <div class="space-y-4 pt-8 border-t border-border-default">
              <h4 class="text-sm font-bold uppercase tracking-widest text-foreground-subtle font-mono">This course includes:</h4>
              <ul class="space-y-3">
                <li v-for="item in includes" :key="item.label" class="flex items-center gap-3 text-sm text-foreground-muted">
                  <component :is="item.icon" class="w-4 h-4 text-accent" />
                  {{ item.label }}
                </li>
              </ul>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
import axios from 'axios'
import { 
  Loader2, 
  Users, 
  Clock, 
  Star, 
  Play, 
  FileText, 
  Check, 
  ChevronRight,
  ArrowRight,
  Infinity,
  Smartphone,
  Award
} from 'lucide-vue-next'

const route = useRoute()
const auth = useAuthStore()
const config = useRuntimeConfig()
const course = ref(null)
const loading = ref(true)

const includes = [
  { label: 'Full lifetime access', icon: Infinity },
  { label: 'Access on mobile and TV', icon: Smartphone },
  { label: 'Certificate of completion', icon: Award }
]

const totalLessonsCount = computed(() => {
  if (!course.value?.modules) return 0
  return course.value.modules.reduce((acc, mod) => acc + (mod.lessons?.length || 0), 0)
})

const completedLessonsCount = computed(() => {
  if (!course.value?.modules) return 0
  return course.value.modules.reduce((acc, mod) => {
    return acc + (mod.lessons?.filter(l => l.is_completed).length || 0)
  }, 0)
})

const progressPercentage = computed(() => {
  if (totalLessonsCount.value === 0) return 0
  return Math.round((completedLessonsCount.value / totalLessonsCount.value) * 100)
})

const nextLessonUrl = computed(() => {
  if (!course.value?.modules || course.value.modules.length === 0) return '/courses'
  
  // Find first uncompleted lesson
  for (const mod of course.value.modules) {
    if (mod.lessons) {
      for (const lesson of mod.lessons) {
        if (!lesson.is_completed) return `/lessons/${lesson.slug}`
      }
    }
  }
  
  // If all completed, return first lesson if exists
  const firstModule = course.value.modules[0]
  if (firstModule?.lessons?.[0]) {
    return `/lessons/${firstModule.lessons[0].slug}`
  }
  
  return '/courses'
})

onMounted(async () => {
  try {
    const headers = {}
    if (auth.token) {
      headers.Authorization = `Bearer ${auth.token}`
    }
    const response = await axios.get(`${config.public.apiBase}/courses/${route.params.slug}`, { headers })
    course.value = response.data.data
  } catch (err) {
    console.error('Failed to fetch course', err)
  } finally {
    loading.value = false
  }
})
</script>
