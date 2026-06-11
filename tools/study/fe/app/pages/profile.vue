<template>
  <div class="max-w-4xl mx-auto space-y-12 pb-20">
    <section class="space-y-4">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 border border-accent/20 mb-2">
        <span class="label-mono !text-[10px]">Account Settings</span>
      </div>
      <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gradient">Your Profile</h1>
      <p class="text-xl text-foreground-muted max-w-2xl leading-relaxed">
        Manage your personal information and learning preferences.
      </p>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Profile Sidebar -->
      <aside class="space-y-6">
        <div class="card-glass p-8 text-center space-y-6">
          <div class="relative inline-block group">
            <div class="w-32 h-32 rounded-full bg-accent/10 border-2 border-accent/20 flex items-center justify-center overflow-hidden">
              <User class="w-16 h-16 text-accent" />
            </div>
            <button class="absolute bottom-0 right-0 w-10 h-10 rounded-full bg-bg-base border border-border-default flex items-center justify-center text-foreground-muted hover:text-accent transition-colors shadow-lg">
              <Camera class="w-5 h-5" />
            </button>
          </div>
          <div class="space-y-1">
            <h3 class="text-xl font-bold">{{ auth.user?.name }}</h3>
            <p class="text-sm text-foreground-muted">{{ auth.user?.email }}</p>
          </div>
          <div class="pt-4 border-t border-border-default">
            <div class="flex justify-center gap-2">
              <span class="px-2 py-1 rounded-md bg-accent/10 text-accent text-[10px] font-bold uppercase tracking-wider">
                Member since 2026
              </span>
            </div>
          </div>
        </div>

        <div class="card-glass p-6 space-y-4">
          <h4 class="font-bold text-sm uppercase tracking-widest text-foreground-subtle font-mono">Connected Accounts</h4>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 rounded-xl bg-bg-base/50 border border-border-default">
              <div class="flex items-center gap-3">
                <Github class="w-5 h-5" />
                <span class="text-sm font-medium">GitHub</span>
              </div>
              <span class="text-xs text-foreground-subtle">Not linked</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-xl bg-bg-base/50 border border-border-default">
              <div class="flex items-center gap-3">
                <Mail class="w-5 h-5" />
                <span class="text-sm font-medium">Google</span>
              </div>
              <span class="text-xs text-green-500 font-medium">Linked</span>
            </div>
          </div>
        </div>
      </aside>

      <!-- Profile Form -->
      <div class="md:col-span-2">
        <div class="card-glass p-8 md:p-10">
          <form @submit.prevent="updateProfile" class="space-y-8">
            <div v-if="successMsg" class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-sm flex items-center gap-3 animate-fade-in">
              <CheckCircle2 class="w-5 h-5" />
              {{ successMsg }}
            </div>
            <div v-if="errorMsg" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm flex items-center gap-3 animate-fade-in">
              <AlertCircle class="w-5 h-5" />
              {{ errorMsg }}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-sm font-medium text-foreground-muted ml-1">Full Name</label>
                <div class="relative group">
                  <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors" />
                  <input v-model="form.name" type="text" placeholder="Your Name" class="input-field !pl-10" required />
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-foreground-muted ml-1">Learning Level</label>
                <div class="relative group">
                  <GraduationCap class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle group-focus-within:text-accent transition-colors" />
                  <select v-model="form.level" class="input-field !pl-10 appearance-none">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                  </select>
                  <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foreground-subtle pointer-events-none" />
                </div>
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-foreground-muted ml-1">Your Learning Goal</label>
              <textarea v-model="form.goal" rows="4" class="input-field !resize-none" placeholder="E.g., I want to reach IELTS 7.5 by the end of the year to apply for a master's degree in the UK."></textarea>
              <p class="text-[10px] text-foreground-subtle ml-1">A clear goal helps us personalize your curriculum.</p>
            </div>

            <div class="pt-4 border-t border-border-default flex justify-end">
              <button type="submit" :disabled="saving" class="btn-primary min-w-[160px]">
                <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ saving ? 'Saving Changes...' : 'Save Changes' }}
              </button>
            </div>
          </form>
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
  Camera, 
  Github, 
  Mail, 
  CheckCircle2, 
  AlertCircle, 
  GraduationCap, 
  ChevronDown, 
  Save, 
  Loader2 
} from 'lucide-vue-next'

const auth = useAuthStore()
const config = useRuntimeConfig()
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const form = reactive({
  name: auth.user?.name || '',
  level: auth.user?.profile?.level || 'beginner',
  goal: auth.user?.profile?.goal || ''
})

definePageMeta({
  middleware: 'auth'
})

const updateProfile = async () => {
  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''
  
  try {
    const response = await axios.put(`${config.public.apiBase}/profile`, form, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    auth.user = response.data.data
    successMsg.value = 'Profile updated successfully!'
  } catch (err) {
    console.error('Update profile error', err)
    errorMsg.value = err.response?.data?.message || 'Failed to update profile'
  } finally {
    saving.value = false
  }
}
</script>
