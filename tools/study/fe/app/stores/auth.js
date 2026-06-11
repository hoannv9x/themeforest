import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => {
    const token = useCookie('token')
    return {
      user: null, 
      token: token.value,
      loading: false,
      error: null,
      validationErrors: {}
    }
  },
  getters: {
    isLoggedIn: (state) => !!state.token,
  },
  actions: {
    async login(email, password) {
      this.loading = true
      this.error = null
      this.validationErrors = {}
      try {
        const api = useApi()
        const response = await api.post('/login', { email, password })
        const tokenCookie = useCookie('token', { maxAge: 60 * 60 * 24 * 7 }) // 1 week

        this.token = response.data.data.token
        tokenCookie.value = this.token
        this.user = response.data.data.user

        return true
      } catch (err) {
        this.handleError(err, 'Login failed')
        return false
      } finally {
        this.loading = false
      }
    },
    async register(name, email, password, password_confirmation) {
      this.loading = true
      this.error = null
      this.validationErrors = {}
      try {
        const api = useApi()
        const response = await api.post('/register', {
          name,
          email,
          password,
          password_confirmation
        })
        const tokenCookie = useCookie('token', { maxAge: 60 * 60 * 24 * 7 })

        this.token = response.data.data.token
        tokenCookie.value = this.token
        this.user = response.data.data.user

        return true
      } catch (err) {
        this.handleError(err, 'Registration failed')
        return false
      } finally {
        this.loading = false
      }
    },
    async logout() {
      try {
        const api = useApi()
        await api.post('/logout')
      } catch (err) {
        console.error('Logout error', err)
      } finally {
        const tokenCookie = useCookie('token')
        this.token = null
        this.user = null
        tokenCookie.value = null
        navigateTo('/login')
      }
    },
    async fetchUser() {
      if (!this.token) return

      try {
        const api = useApi()
        const response = await api.get('/user')
        this.user = response.data.data
      } catch (err) {
        // Handled by interceptor
      }
    },
    handleError(err, defaultMessage) {
      if (err.response?.status === 422) {
        this.validationErrors = err.response.data.errors || {}
        this.error = err.response.data.message || 'Validation failed'
      } else {
        this.error = err.response?.data?.message || defaultMessage
      }
    },
    initialize() {
      if (this.token && !this.user) {
        this.fetchUser()
      }
    }
  }
})
