<template>
  <div class="max-w-5xl mx-auto space-y-8 pb-20">
    <div v-if="loading" class="flex flex-col items-center justify-center py-40 space-y-4">
      <Loader2 class="w-12 h-12 text-accent animate-spin" />
      <p class="text-foreground-muted font-mono uppercase tracking-widest text-xs">
        Loading lesson content...
      </p>
    </div>

    <div v-else-if="lesson" class="space-y-8 animate-fade-in">
      <!-- Breadcrumbs & Header -->
      <nav class="flex items-center gap-4">
        <NuxtLink
          :to="`/courses/${lesson.module.course.slug}`"
          class="btn-ghost !px-3 !py-1.5 text-xs"
        >
          <ChevronLeft class="w-4 h-4" />
          Back to Course
        </NuxtLink>
        <div class="h-4 w-px bg-border-default"></div>
        <span class="text-xs font-medium text-foreground-subtle">{{
          lesson.module.title
        }}</span>
      </nav>

      <div class="space-y-4">
        <h1 class="text-3xl md:text-5xl font-bold tracking-tight text-gradient">
          {{ lesson.title }}
        </h1>
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-2 text-sm text-foreground-muted">
            <div class="w-5 h-5 rounded-md bg-accent/10 flex items-center justify-center">
              <Play v-if="lesson.content_type === 'video'" class="w-3 h-3 text-accent" />
              <FileText v-else class="w-3 h-3 text-accent" />
            </div>
            <span class="capitalize">{{ lesson.content_type }} Lesson</span>
          </div>
          <div class="flex items-center gap-2 text-sm text-foreground-muted">
            <Clock class="w-4 h-4 text-foreground-subtle" />
            <span>15 min read</span>
          </div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div v-if="!showQuiz" class="card-glass overflow-hidden">
        <!-- Video Player if applicable -->
        <div
          v-if="lesson.content_type === 'video'"
          class="aspect-video bg-bg-deep relative group"
        >
          <iframe
            v-if="lesson.video_url"
            class="w-full h-full"
            :src="lesson.video_url"
            title="Lesson video"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
          <div v-else class="absolute inset-0 flex items-center justify-center">
            <div class="text-center space-y-4">
              <div
                class="w-20 h-20 rounded-full bg-accent/20 border border-accent/40 flex items-center justify-center backdrop-blur-md group-hover:scale-110 transition-transform cursor-pointer"
              >
                <Play class="w-10 h-10 text-white fill-white" />
              </div>
              <p class="text-foreground-muted font-medium">Video URL not available</p>
            </div>
          </div>
        </div>

        <div class="p-8 md:p-12 space-y-8">
          <div
            class="prose prose-invert max-w-none prose-p:text-foreground-muted prose-headings:text-foreground prose-strong:text-foreground prose-a:text-accent"
          >
            <div class="text-lg leading-relaxed whitespace-pre-wrap text-foreground/90">
              {{ lesson.content }}
            </div>
          </div>

          <!-- Interaction Footer -->
          <div
            class="pt-12 border-t border-border-default flex flex-col sm:flex-row items-center justify-between gap-6"
          >
            <div class="flex items-center gap-4">
              <div
                v-if="lesson.is_completed"
                class="flex items-center gap-3 px-4 py-2 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500"
              >
                <div
                  class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center"
                >
                  <Check class="w-4 h-4 text-white" />
                </div>
                <span class="text-sm font-bold">Lesson Completed</span>
              </div>
              <div
                v-else
                class="flex items-center gap-3 px-4 py-2 rounded-xl bg-bg-base/50 border border-border-default text-foreground-subtle"
              >
                <div
                  class="w-6 h-6 rounded-full bg-border-default flex items-center justify-center"
                >
                  <div class="w-2 h-2 rounded-full bg-foreground-subtle"></div>
                </div>
                <span class="text-sm font-medium">Mark as complete when finished</span>
              </div>
            </div>

            <div class="flex gap-4">
              <button
                v-if="!lesson.is_completed"
                @click="markComplete"
                :disabled="completing"
                class="btn-primary"
              >
                <Loader2 v-if="completing" class="w-4 h-4 animate-spin" />
                <Check v-else class="w-4 h-4" />
                {{ completing ? "Saving Progress..." : "Mark as Completed" }}
              </button>
              <button 
                v-if="lesson.is_completed && lesson.quiz"
                @click="startQuiz"
                class="btn-primary bg-indigo-600 hover:bg-indigo-500"
              >
                <Zap class="w-4 h-4" />
                Take Lesson Quiz
              </button>
              <NuxtLink v-else-if="lesson.is_completed" :to="nextLessonUrl" class="btn-primary group">
                Next Lesson
                <ArrowRight
                  class="w-4 h-4 transition-transform group-hover:translate-x-1"
                />
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Quiz Section -->
      <div v-else-if="showQuiz" class="animate-fade-in">
        <QuizResult v-if="quizResult" :result="quizResult" :back-url="`/courses/${lesson.module.course.slug}`" @retry="retryQuiz" />
        <QuizPlayer v-else :quiz="fullQuiz" @completed="onQuizCompleted" />
      </div>

      <!-- Navigation Between Lessons -->
      <div class="grid grid-cols-2 gap-4">
        <button class="card-glass p-6 text-left group hover:border-accent/30">
          <div class="flex items-center gap-3 text-xs text-foreground-subtle mb-1">
            <ChevronLeft class="w-3 h-3" />
            Previous Lesson
          </div>
          <div class="font-bold group-hover:text-accent transition-colors">
            Introduction to Phonetics
          </div>
        </button>
        <button class="card-glass p-6 text-right group hover:border-accent/30">
          <div
            class="flex items-center justify-end gap-3 text-xs text-foreground-subtle mb-1"
          >
            Next Lesson
            <ChevronRight class="w-3 h-3" />
          </div>
          <div class="font-bold group-hover:text-accent transition-colors">
            Vowel Sounds Part 1
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";
import axios from "axios";
import {
  ChevronLeft,
  ChevronRight,
  Play,
  FileText,
  Clock,
  Check,
  Loader2,
  ArrowRight,
  Zap
} from "lucide-vue-next";

const route = useRoute();
const auth = useAuthStore();
const config = useRuntimeConfig();
const lesson = ref(null);
const loading = ref(true);
const completing = ref(false);

// Quiz State
const showQuiz = ref(false)
const fullQuiz = ref(null)
const quizResult = ref(null)

const startQuiz = async () => {
  if (!lesson.value?.quiz) return
  
  loading.value = true
  try {
    const api = useApi()
    const response = await api.get(`/quizzes/${lesson.value.quiz.id}`)
    fullQuiz.value = response.data.data
    showQuiz.value = true
  } catch (err) {
    console.error('Failed to fetch quiz', err)
    alert('Failed to start quiz. Please try again.')
  } finally {
    loading.value = false
  }
}

const onQuizCompleted = (result) => {
  quizResult.value = result
}

const retryQuiz = () => {
  quizResult.value = null
}

definePageMeta({
  middleware: "auth",
});

const fetchLesson = async () => {
  try {
    const response = await axios.get(
      `${config.public.apiBase}/lessons/${route.params.slug}`,
      {
        headers: { Authorization: `Bearer ${auth.token}` },
      }
    );
    lesson.value = response.data.data;
  } catch (err) {
    console.error("Failed to fetch lesson", err);
  } finally {
    loading.value = false;
  }
};

const markComplete = async () => {
  completing.value = true;
  try {
    await axios.post(
      `${config.public.apiBase}/lessons/${lesson.value.id}/complete`,
      {},
      {
        headers: { Authorization: `Bearer ${auth.token}` },
      }
    );
    lesson.value.is_completed = true;
  } catch (err) {
    console.error("Failed to complete lesson", err);
    alert("Failed to save progress. Please try again.");
  } finally {
    completing.value = false;
  }
};

const nextLessonUrl = computed(() => {
  // This is a placeholder, usually we'd get this from the API or a store
  return `/courses/${lesson.value?.module?.course?.slug}`;
});

onMounted(fetchLesson);
</script>
