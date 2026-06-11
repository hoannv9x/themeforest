<template>
  <div v-if="quiz" class="space-y-8">
    <!-- Quiz Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold tracking-tight">{{ quiz.title }}</h2>
        <p class="text-sm text-foreground-muted">Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}</p>
      </div>
      <div class="flex items-center gap-4">
        <div class="px-3 py-1 rounded-full bg-accent/10 border border-accent/20 text-accent text-xs font-mono">
          Points: {{ quiz.questions[currentQuestionIndex].points }}
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="h-1.5 w-full bg-bg-base rounded-full border border-border-default overflow-hidden">
      <div class="h-full bg-accent transition-all duration-500" :style="{ width: progress + '%' }"></div>
    </div>

    <!-- Question Content -->
    <div class="card-glass p-8 md:p-12 space-y-8 min-h-[400px] flex flex-col justify-center">
      <div class="space-y-6">
        <h3 class="text-xl md:text-2xl font-medium leading-relaxed">
          {{ currentQuestion.content }}
        </h3>

        <!-- Answers -->
        <div class="grid grid-cols-1 gap-4">
          <button 
            v-for="answer in currentQuestion.answers" 
            :key="answer.id"
            @click="selectAnswer(answer)"
            class="flex items-center gap-4 p-4 rounded-xl border transition-all text-left group"
            :class="[
              selectedAnswerId === answer.id 
                ? 'bg-accent/10 border-accent shadow-accent-glow' 
                : 'bg-bg-base/50 border-border-default hover:border-border-hover'
            ]"
          >
            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
              :class="selectedAnswerId === answer.id ? 'border-accent bg-accent text-white' : 'border-border-default group-hover:border-border-hover'">
              <Check v-if="selectedAnswerId === answer.id" class="w-3 h-3" />
            </div>
            <span class="text-lg" :class="selectedAnswerId === answer.id ? 'text-foreground font-medium' : 'text-foreground-muted'">
              {{ answer.content }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex items-center justify-between pt-4">
      <button 
        @click="prevQuestion" 
        :disabled="currentQuestionIndex === 0"
        class="btn-secondary !px-6 disabled:opacity-30"
      >
        <ChevronLeft class="w-4 h-4" />
        Previous
      </button>

      <button 
        v-if="currentQuestionIndex < quiz.questions.length - 1"
        @click="nextQuestion" 
        :disabled="!selectedAnswerId"
        class="btn-primary !px-8"
      >
        Next
        <ChevronRight class="w-4 h-4" />
      </button>

      <button 
        v-else
        @click="submitQuiz" 
        :disabled="!selectedAnswerId || submitting"
        class="btn-primary !px-10 bg-green-600 hover:bg-green-500 shadow-green-500/20"
      >
        <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
        <Send v-else class="w-4 h-4" />
        {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { Check, ChevronLeft, ChevronRight, Send, Loader2 } from 'lucide-vue-next'

const props = defineProps({
  quiz: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['completed'])

const currentQuestionIndex = ref(0)
const answers = ref({}) // questionId -> answerId
const submitting = ref(false)

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value])
const selectedAnswerId = computed(() => answers.value[currentQuestion.value.id])
const progress = computed(() => ((currentQuestionIndex.value + 1) / props.quiz.questions.length) * 100)

const selectAnswer = (answer) => {
  answers.value[currentQuestion.value.id] = answer.id
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < props.quiz.questions.length - 1) {
    currentQuestionIndex.value++
  }
}

const prevQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

const submitQuiz = async () => {
  submitting.value = true
  try {
    const api = useApi()
    const response = await api.post(`/quizzes/${props.quiz.id}/submit`, {
      answers: Object.entries(answers.value).map(([questionId, answerId]) => ({
        question_id: parseInt(questionId),
        answer_id: answerId
      }))
    })
    emit('completed', response.data.data)
  } catch (err) {
    console.error('Failed to submit quiz', err)
    alert('Failed to submit quiz. Please try again.')
  } finally {
    submitting.value = false
  }
}
</script>
