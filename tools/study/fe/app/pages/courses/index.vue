<template>
  <div class="space-y-12 pb-20">
    <!-- Header Section -->
    <section class="space-y-4">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 border border-accent/20 mb-2">
        <span class="label-mono !text-[10px]">Curated Library</span>
      </div>
      <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-gradient">Master Your English</h1>
      <p class="text-xl text-foreground-muted max-w-2xl leading-relaxed">
        Explore our high-end courses designed by linguistic experts to take your skills to the next level.
      </p>
    </section>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col md:flex-row gap-4 items-center">
      <div class="relative flex-1 group w-full">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors" />
        <input type="text" placeholder="Search courses..." class="input-field !pl-10" />
      </div>
      <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
        <button v-for="cat in categories" :key="cat" class="px-4 py-2 rounded-lg bg-surface border border-border-default text-sm font-medium whitespace-nowrap hover:border-accent transition-colors">
          {{ cat }}
        </button>
      </div>
    </div>

    <!-- Course Grid -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="n in 6" :key="n" class="card-glass p-4 space-y-4 animate-pulse">
        <div class="aspect-video bg-bg-base/50 rounded-xl"></div>
        <div class="space-y-2">
          <div class="h-6 w-3/4 bg-bg-base/50 rounded"></div>
          <div class="h-4 w-full bg-bg-base/50 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="course in courses" :key="course.id" class="card-glass group hover:translate-y-[-4px] transition-all">
        <div class="p-4 space-y-4">
          <div class="relative aspect-video overflow-hidden rounded-xl bg-bg-base">
            <img :src="course.thumbnail" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
            <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-xl"></div>
            <div class="absolute top-3 left-3 px-2 py-1 rounded-md bg-bg-base/80 backdrop-blur-md text-[10px] font-bold uppercase tracking-widest border border-white/10">
              {{ course.status }}
            </div>
          </div>
          
          <div class="space-y-2">
            <h3 class="text-xl font-bold group-hover:text-accent transition-colors">{{ course.title }}</h3>
            <p class="text-sm text-foreground-muted line-clamp-2 leading-relaxed">
              {{ course.description }}
            </p>
          </div>

          <div class="pt-4 flex items-center justify-between border-t border-border-default">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-accent/10 flex items-center justify-center">
                <Users class="w-3 h-3 text-accent" />
              </div>
              <span class="text-xs text-foreground-subtle">1.2k Students</span>
            </div>
            <NuxtLink :to="`/courses/${course.slug}`" class="text-sm font-bold text-accent group-hover:text-accent-bright flex items-center gap-1 transition-colors">
              View Details <ChevronRight class="w-4 h-4" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { Search, Users, ChevronRight } from 'lucide-vue-next'

const config = useRuntimeConfig()
const courses = ref([])
const loading = ref(true)
const categories = ['All', 'Business', 'Speaking', 'IELTS', 'Grammar', 'Vocabulary']

onMounted(async () => {
  try {
    const response = await axios.get(`${config.public.apiBase}/courses`)
    courses.value = response.data.data
  } catch (err) {
    console.error('Failed to fetch courses', err)
  } finally {
    loading.value = false
  }
})
</script>
