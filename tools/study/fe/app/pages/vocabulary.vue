<template>
  <div class="space-y-12 pb-20">
    <!-- Header Section -->
    <section class="space-y-4">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 border border-accent/20 mb-2">
        <span class="label-mono !text-[10px]">Vocabulary System</span>
      </div>
      <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-gradient">Master New Words</h1>
      <p class="text-xl text-foreground-muted max-w-2xl leading-relaxed">
        Build your lexicon with our interactive vocabulary system and smart flashcards.
      </p>
    </section>

    <!-- Search & Filter -->
    <div class="flex flex-col md:flex-row gap-4 items-center">
      <div class="relative flex-1 group w-full">
        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-foreground-subtle group-focus-within:text-accent transition-colors" />
        <input 
          v-model="searchQuery" 
          @input="handleSearch"
          type="text" 
          placeholder="Search for a word or meaning..." 
          class="input-field !pl-12 !py-4 text-lg" 
        />
      </div>
      <div class="flex gap-2">
        <button 
          v-for="diff in ['all', 'beginner', 'intermediate', 'advanced']" 
          :key="diff"
          @click="difficulty = diff"
          class="px-6 py-2 rounded-xl border transition-all font-medium capitalize"
          :class="difficulty === diff ? 'bg-accent border-accent text-white shadow-accent-glow' : 'bg-surface border-border-default hover:border-border-hover text-foreground-muted'"
        >
          {{ diff }}
        </button>
      </div>
    </div>

    <!-- Mode Toggle -->
    <div class="flex justify-center">
      <div class="p-1 rounded-2xl bg-bg-elevated border border-border-default flex">
        <button 
          @click="viewMode = 'list'"
          class="px-8 py-2 rounded-xl transition-all flex items-center gap-2 font-medium"
          :class="viewMode === 'list' ? 'bg-bg-base shadow-lg text-foreground' : 'text-foreground-muted hover:text-foreground'"
        >
          <List class="w-4 h-4" />
          Word List
        </button>
        <button 
          @click="viewMode = 'flashcard'"
          class="px-8 py-2 rounded-xl transition-all flex items-center gap-2 font-medium"
          :class="viewMode === 'flashcard' ? 'bg-bg-base shadow-lg text-foreground' : 'text-foreground-muted hover:text-foreground'"
        >
          <Zap class="w-4 h-4" />
          Flashcards
        </button>
      </div>
    </div>

    <!-- Word List View -->
    <div v-if="viewMode === 'list'" class="space-y-4">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <Loader2 class="w-12 h-12 text-accent animate-spin" />
        <p class="text-foreground-muted font-mono uppercase tracking-widest text-xs">Loading vocabulary...</p>
      </div>

      <div v-else-if="vocabularies.length === 0" class="text-center py-20 card-glass space-y-4">
        <div class="w-16 h-16 rounded-full bg-bg-base border border-border-default flex items-center justify-center mx-auto text-foreground-subtle">
          <BookOpen class="w-8 h-8" />
        </div>
        <p class="text-foreground-muted">No words found matching your criteria.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="vocab in vocabularies" :key="vocab.id" class="card-glass p-6 group hover:translate-x-1 transition-all">
          <div class="flex justify-between items-start gap-4">
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <h3 class="text-2xl font-bold group-hover:text-accent transition-colors">{{ vocab.word }}</h3>
                <span class="text-sm text-foreground-subtle font-mono">{{ vocab.ipa }}</span>
                <button @click="playAudio(vocab.audio_url)" class="p-1.5 rounded-lg bg-surface text-foreground-subtle hover:text-accent transition-colors">
                  <Volume2 class="w-4 h-4" />
                </button>
              </div>
              <p class="text-lg text-foreground/90 font-medium">{{ vocab.meaning }}</p>
              <div class="space-y-1">
                <p v-for="ex in vocab.examples" :key="ex.id" class="text-sm text-foreground-muted italic leading-relaxed">
                  "{{ ex.example }}"
                </p>
              </div>
            </div>
            
            <div class="flex flex-col items-end gap-3">
              <button 
                @click="toggleFavorite(vocab)"
                class="p-2 rounded-xl transition-all"
                :class="vocab.is_favorite ? 'bg-red-500/10 text-red-500' : 'bg-surface text-foreground-subtle hover:text-red-500'"
              >
                <Heart class="w-5 h-5" :class="{ 'fill-current': vocab.is_favorite }" />
              </button>
              <span class="px-2 py-1 rounded-md bg-accent/10 text-accent text-[10px] font-bold uppercase tracking-widest">
                {{ vocab.difficulty }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Flashcard View -->
    <div v-else-if="viewMode === 'flashcard'" class="max-w-2xl mx-auto pt-10">
      <div v-if="vocabularies.length > 0" class="space-y-12">
        <div 
          class="relative h-[400px] perspective-1000 cursor-pointer"
          @click="isFlipped = !isFlipped"
        >
          <div 
            class="w-full h-full transition-all duration-700 preserve-3d relative"
            :class="{ 'rotate-y-180': isFlipped }"
          >
            <!-- Front -->
            <div class="absolute inset-0 backface-hidden card-glass p-12 flex flex-col items-center justify-center text-center space-y-6">
              <div class="label-mono mb-4">English Word</div>
              <h3 class="text-5xl font-bold tracking-tight">{{ currentFlashcard.word }}</h3>
              <p class="text-xl text-foreground-subtle font-mono">{{ currentFlashcard.ipa }}</p>
              <div class="mt-8 p-3 rounded-full bg-accent/10 border border-accent/20 animate-pulse">
                <MousePointer2 class="w-5 h-5 text-accent" />
              </div>
              <p class="text-xs text-foreground-subtle uppercase tracking-widest font-mono">Click to flip</p>
            </div>
            
            <!-- Back -->
            <div class="absolute inset-0 backface-hidden rotate-y-180 card-glass p-12 flex flex-col items-center justify-center text-center space-y-6 border-accent/30">
              <div class="label-mono mb-4 text-accent">Vietnamese Meaning</div>
              <h3 class="text-4xl font-bold tracking-tight">{{ currentFlashcard.meaning }}</h3>
              <div class="space-y-2 pt-4">
                <p v-for="ex in currentFlashcard.examples" :key="ex.id" class="text-lg text-foreground-muted italic">
                  "{{ ex.example }}"
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center justify-center gap-8">
          <button @click="prevCard" class="btn-secondary !rounded-full !p-4">
            <ChevronLeft class="w-6 h-6" />
          </button>
          <div class="text-lg font-mono text-foreground-muted">
            {{ currentCardIndex + 1 }} / {{ vocabularies.length }}
          </div>
          <button @click="nextCard" class="btn-secondary !rounded-full !p-4">
            <ChevronRight class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  Search, 
  List, 
  Zap, 
  Volume2, 
  Heart, 
  ChevronLeft, 
  ChevronRight, 
  Loader2,
  BookOpen,
  MousePointer2
} from 'lucide-vue-next'
import debounce from 'lodash/debounce'

const vocabularies = ref([])
const loading = ref(true)
const viewMode = ref('list')
const searchQuery = ref('')
const difficulty = ref('all')

// Flashcard state
const currentCardIndex = ref(0)
const isFlipped = ref(false)
const currentFlashcard = computed(() => vocabularies.value[currentCardIndex.value])

const fetchVocabularies = async () => {
  loading.value = true
  try {
    const api = useApi()
    const response = await api.get('/vocabularies', {
      params: {
        search: searchQuery.value,
        difficulty: difficulty.value === 'all' ? undefined : difficulty.value
      }
    })
    vocabularies.value = response.data.data.data
  } catch (err) {
    console.error('Failed to fetch vocabularies', err)
  } finally {
    loading.value = false
  }
}

const handleSearch = debounce(() => {
  fetchVocabularies()
}, 500)

watch(difficulty, () => {
  fetchVocabularies()
})

const toggleFavorite = async (vocab) => {
  try {
    const api = useApi()
    const response = await api.post(`/vocabularies/${vocab.id}/favorite`)
    vocab.is_favorite = response.data.data.is_favorite
  } catch (err) {
    console.error('Failed to toggle favorite', err)
  }
}

const playAudio = (url) => {
  if (!url) return
  const audio = new Audio(url)
  audio.play()
}

const nextCard = () => {
  isFlipped.value = false
  setTimeout(() => {
    if (currentCardIndex.value < vocabularies.value.length - 1) {
      currentCardIndex.value++
    } else {
      currentCardIndex.value = 0
    }
  }, 150)
}

const prevCard = () => {
  isFlipped.value = false
  setTimeout(() => {
    if (currentCardIndex.value > 0) {
      currentCardIndex.value--
    } else {
      currentCardIndex.value = vocabularies.value.length - 1
    }
  }, 150)
}

onMounted(fetchVocabularies)

definePageMeta({
  middleware: 'auth'
})
</script>

<style scoped>
.perspective-1000 {
  perspective: 1000px;
}
.preserve-3d {
  transform-style: preserve-3d;
}
.backface-hidden {
  backface-visibility: hidden;
}
.rotate-y-180 {
  transform: rotateY(180deg);
}
</style>
