import { defineStore } from 'pinia'
import type { User, LoginCredentials, AuthResponse, ApiResponse } from '~/types'

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  loading: boolean
}

const cookieOptions = {
  path: '/',
  sameSite: 'lax' as const,
  maxAge: 60 * 60 * 24 * 7,
}

const useAuthCookies = () => {
  const token = useCookie<string | null>('auth_token', { ...cookieOptions, default: () => null })
  const user = useCookie<User | null>('auth_user', { ...cookieOptions, default: () => null })
  return { token, user }
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isAuthenticated: false,
    loading: false,
  }),

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    isLoggedIn: (state) => state.isAuthenticated,
  },

  actions: {
    async login(credentials: LoginCredentials): Promise<boolean> {
      this.loading = true
      const api = useApi()

      try {
        const response = await api.post<ApiResponse<AuthResponse>>('/auth/login', credentials)

        if (response.success && response.data) {
          this.setAuth(response.data)
          return true
        }

        return false
      } catch (error) {
        console.error('Erro no login:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout(): Promise<void> {
      const api = useApi()

      if(this.token){
        try {
          await api.post('/auth/logout')
        } catch (error) {
          console.error('Erro no logout:', error)
        } finally {
          this.clearAuth()
        }
      }
    },

    async fetchUser(): Promise<void> {
      if (!this.token) return

      const api = useApi()

      try {
        const response = await api.get<ApiResponse<User>>('/auth/me')
        if (response.success && response.data) {
          this.user = response.data
          const { user } = useAuthCookies()
          user.value = response.data
        }
      } catch (error) {
        console.error('Erro ao buscar usuário:', error)
        this.clearAuth()
      }
    },

    async refreshToken(): Promise<boolean> {
      const api = useApi()

      try {
        const response = await api.post<ApiResponse<{ token: string }>>('/auth/refresh')
        if (response.success && response.data) {
          this.token = response.data.token
          const { token } = useAuthCookies()
          token.value = response.data.token
          return true
        }
        return false
      } catch (error) {
        console.error('Erro ao atualizar token:', error)
        this.clearAuth()
        return false
      }
    },

    setAuth(data: AuthResponse): void {
      this.user = data.user
      this.token = data.token
      this.isAuthenticated = true

      const { token, user } = useAuthCookies()
      token.value = data.token
      user.value = data.user
    },

    clearAuth(): void {
      this.user = null
      this.token = null
      this.isAuthenticated = false

      const { token, user } = useAuthCookies()
      token.value = null
      user.value = null

      if (import.meta.client) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },

    initAuth(): void {
      if (this.isAuthenticated && this.token) return

      const { token, user } = useAuthCookies()

      if (token.value && user.value) {
        this.token = token.value
        this.user = user.value
        this.isAuthenticated = true
        return
      }

      // Migra sessão antiga salva apenas no localStorage
      if (import.meta.client) {
        const lsToken = localStorage.getItem('token')
        const lsUser = localStorage.getItem('user')

        if (lsToken && lsUser) {
          this.token = lsToken
          this.user = JSON.parse(lsUser)
          this.isAuthenticated = true
          token.value = lsToken
          user.value = this.user
          localStorage.removeItem('token')
          localStorage.removeItem('user')
        }
      }
    },
  },
})
