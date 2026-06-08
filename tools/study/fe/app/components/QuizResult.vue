<template>
  <div class="card-glass p-12 text-center space-y-8 animate-fade-in">
    <!-- Icon & Status -->
    <div class="space-y-4">
      <div class="relative inline-block">
        <div class="w-24 h-24 rounded-full flex items-center justify-center relative z-10"
          :class="isPassed ? 'bg-green-500/20 border-2 border-green-500/30' : 'bg-red-500/20 border-2 border-red-500/30'">
          <Trophy v-if="isPassed" class="w-12 h-12 text-green-500" />
          <AlertCircle v-else class="w-12 h-12 text-red-500" />
        </div>
        <div class="absolute inset-0 blur-2xl opacity-50"
          :class="isPassed ? 'bg-green-500' : 'bg-red-500'"></div>
      </div>
      
      <h2 class="text-4xl font-bold tracking-tight">
        {{ isPassed ? 'Excellent Work!' : 'Keep Practicing!' }}
      </h2>
      <p class="text-foreground-muted text-lg">
        {{ isPassed ? 'You have successfully mastered this quiz.' : 'You were so close! Review the material and try again.' }}
      </p>
    </div>

    <!-- Score Stats -->
    <div class="grid grid-cols-2 gap-4 max-w-md mx-auto">
      <div class="p-6 rounded-2xl bg-bg-base/50 border border-border-default space-y-1">
        <div class="text-3xl font-bold text-accent">{{ result.score }}%</div>
        <div class="text-xs font-mono uppercase tracking-widest text-foreground-subtle">Your Score</div>
      </div>
      <div class="p-6 rounded-2xl bg-bg-base/50 border border-border-default space-y-1">
        <div class="text-3xl font-bold text-foreground">{{ result.correct_answers }}/{{ result.total_questions }}</div>
        <div class="text-xs font-mono uppercase tracking-widest text-foreground-subtle">Correct</div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
      <button @click="$emit('retry')" class="btn-secondary group">
        <RotateCcw class="w-4 h-4 transition-transform group-hover:rotate-[-45deg]" />
        Try Again
      </button>
      <NuxtLink :to="backUrl" class="btn-primary">
        Back to Course
        <ArrowRight class="w-4 h-4" />
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { Trophy, AlertCircle, RotateCcw, ArrowRight } from 'lucide-vue-next'

const props = defineProps({
  result: {
    type: Object,
    required: true
  },
  backUrl: {
    type: String,
    default: '/courses'
  }
})

defineEmits(['retry'])

const isPassed = computed(() => props.result.score >= 80)
</script>
