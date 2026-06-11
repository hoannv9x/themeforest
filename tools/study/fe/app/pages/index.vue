<template>
  <div class="space-y-32 pb-24">
    <!-- Hero Section -->
    <section class="relative pt-20 pb-32 overflow-hidden">
      <div class="text-center space-y-8 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 border border-accent/20 mb-4 animate-fade-in">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
          </span>
          <span class="label-mono !text-[10px]">New: AI Speaking Practice</span>
        </div>
        
        <h1 class="text-6xl md:text-8xl font-bold tracking-tight max-w-4xl mx-auto leading-[1.1]">
          Master English with <br />
          <span class="text-accent-gradient">Precision & Flow</span>
        </h1>
        
        <p class="text-xl text-foreground-muted max-w-2xl mx-auto leading-relaxed">
          The ultimate platform for professionals and students to elevate their English skills through cinematic learning experiences.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <NuxtLink to="/register" class="btn-primary group">
            Start Learning Free
            <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </NuxtLink>
          <NuxtLink to="/courses" class="btn-secondary">
            Explore Courses
          </NuxtLink>
        </div>
      </div>

      <!-- Hero Visual Decoration -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-accent/10 blur-[120px] rounded-full"></div>
      </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="space-y-12">
      <div class="text-center space-y-4">
        <h2 class="text-3xl md:text-5xl font-bold tracking-tight">Everything you need to succeed</h2>
        <p class="text-foreground-muted max-w-xl mx-auto">Our platform combines state-of-the-art technology with proven linguistic methods.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-6 gap-6 auto-rows-[240px]">
        <!-- Hero Feature Card -->
        <div class="md:col-span-4 md:row-span-2 card-glass p-8 flex flex-col justify-between group">
          <div class="space-y-4">
            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center border border-accent/20">
              <Zap class="w-6 h-6 text-accent" />
            </div>
            <h3 class="text-3xl font-bold">Accelerated Learning</h3>
            <p class="text-foreground-muted text-lg max-w-md">
              Our adaptive algorithm identifies your weaknesses and optimizes your study path for maximum efficiency.
            </p>
          </div>
          <div class="relative h-48 mt-8 rounded-xl overflow-hidden border border-border-default bg-bg-base/50 p-4">
            <div class="flex gap-4 items-end h-full">
              <div v-for="h in [40, 70, 45, 90, 65, 80, 55]" :key="h" 
                class="flex-1 bg-accent/20 rounded-t-lg transition-all duration-500 group-hover:bg-accent/40"
                :style="{ height: h + '%' }"></div>
            </div>
          </div>
        </div>

        <!-- Secondary Feature Card -->
        <div class="md:col-span-2 card-glass p-8 space-y-4 hover:translate-y-[-4px] transition-transform">
          <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20">
            <Shield class="w-5 h-5 text-indigo-500" />
          </div>
          <h3 class="text-xl font-bold">Progress Tracking</h3>
          <p class="text-foreground-muted text-sm">Detailed analytics to keep you motivated and on track.</p>
        </div>

        <!-- Another Card -->
        <div class="md:col-span-2 card-glass p-8 space-y-4 hover:translate-y-[-4px] transition-transform">
          <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center border border-purple-500/20">
            <Globe class="w-5 h-5 text-purple-500" />
          </div>
          <h3 class="text-xl font-bold">Learn Anywhere</h3>
          <p class="text-foreground-muted text-sm">Seamless experience across mobile, tablet, and desktop.</p>
        </div>

        <!-- Wide Card -->
        <div class="md:col-span-6 card-glass p-8 flex flex-col md:flex-row items-center gap-8 group">
          <div class="flex-1 space-y-4">
            <div class="label-mono">Upcoming</div>
            <h3 class="text-2xl font-bold">AI Speaking Partner</h3>
            <p class="text-foreground-muted">Practice conversation with our AI that gives real-time pronunciation feedback.</p>
            <button class="btn-secondary !px-4 !py-2 !text-xs">Join Waitlist</button>
          </div>
          <div class="flex-1 w-full flex justify-center">
            <div class="relative w-48 h-48">
              <div class="absolute inset-0 bg-accent/20 rounded-full animate-glow-pulse blur-2xl"></div>
              <div class="relative z-10 w-full h-full rounded-full border-2 border-accent/30 flex items-center justify-center bg-bg-base/80">
                <Mic class="w-12 h-12 text-accent" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-24 border-y border-border-default">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
        <div v-for="stat in stats" :key="stat.label" class="text-center space-y-2">
          <div class="text-4xl font-bold text-gradient">{{ stat.value }}</div>
          <div class="text-sm text-foreground-muted uppercase tracking-widest font-mono">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="card-glass p-12 md:p-24 text-center space-y-8 relative overflow-hidden">
      <div class="absolute inset-0 bg-accent/5 pointer-events-none"></div>
      <div class="relative z-10 space-y-6">
        <h2 class="text-4xl md:text-6xl font-bold tracking-tight">Ready to start your journey?</h2>
        <p class="text-xl text-foreground-muted max-w-2xl mx-auto">Join 10,000+ students already mastering English on our platform.</p>
        <div class="flex justify-center pt-4">
          <NuxtLink to="/register" class="btn-primary !px-12 !py-4 text-lg">
            Get Started Now
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { 
  ArrowRight, 
  Zap, 
  Shield, 
  Globe, 
  Mic,
  BarChart3,
  BookOpen,
  Award
} from 'lucide-vue-next'

const stats = [
  { label: 'Active Students', value: '10K+' },
  { label: 'Expert Courses', value: '200+' },
  { label: 'Success Rate', value: '98%' },
  { label: 'Countries', value: '50+' }
]
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.8s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
