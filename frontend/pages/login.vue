<script setup lang="ts">
import type { LoginCredentials } from '~/types'
import { ApiError } from '~/composables/useApi'

definePageMeta({
  layout: 'auth',
  middleware: 'auth',
})

const authStore = useAuthStore()
const toast = useToast()

const form = reactive<LoginCredentials>({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
  general: '',
})

const loading = ref(false)
const showPassword = ref(false)

const clearErrors = () => {
  errors.email = ''
  errors.password = ''
  errors.general = ''
}

const validate = (): boolean => {
  clearErrors()
  let isValid = true

  if (!form.email) {
    errors.email = 'E-mail é obrigatório'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'E-mail inválido'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Senha é obrigatória'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Senha deve ter no mínimo 6 caracteres'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validate()) return

  loading.value = true
  clearErrors()

  try {
    await authStore.login(form)
    toast.success('Login realizado com sucesso!')
    await navigateTo('/seguros', { replace: true })
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.status === 401) {
        errors.general = 'E-mail ou senha incorretos'
      } else if (error.errors) {
        if (error.errors.email) errors.email = error.errors.email[0]
        if (error.errors.password) errors.password = error.errors.password[0]
      } else {
        errors.general = error.message
      }
    } else {
      errors.general = 'Erro ao fazer login. Tente novamente.'
    }
    toast.error(errors.general || 'Erro ao fazer login')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="p-8">
    <div class="text-center mb-7">
      <h2 class="font-display text-2xl font-extrabold text-primary-800">
        Acesse sua conta
      </h2>
      <p class="text-sm text-gray-500 mt-1">
        Bem-vindo(a) de volta ao <span class="font-semibold text-primary-700">Portal de Seguros</span>
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Alerta de erro -->
      <Transition name="fade">
        <div
          v-if="errors.general"
          class="flex items-start gap-3 bg-danger-50 border-l-4 border-danger-500 text-danger-700 px-4 py-3 rounded-lg"
          role="alert"
        >
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          <span class="text-sm font-medium">{{ errors.general }}</span>
        </div>
      </Transition>

      <!-- Email -->
      <div>
        <label for="email" class="label">E-mail</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </span>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="input pl-10"
            :class="{ 'input-error': errors.email }"
            placeholder="seu@email.com"
            autocomplete="email"
          />
        </div>
        <p v-if="errors.email" class="error-text">{{ errors.email }}</p>
      </div>

      <!-- Senha -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label for="password" class="label !mb-0">Senha</label>
          <a href="#" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">
            Esqueceu?
          </a>
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </span>
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            class="input pl-10 pr-10"
            :class="{ 'input-error': errors.password }"
            placeholder="••••••••"
            autocomplete="current-password"
          />
          <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-primary-600 transition-colors"
            @click="showPassword = !showPassword"
            :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
          >
            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
          </button>
        </div>
        <p v-if="errors.password" class="error-text">{{ errors.password }}</p>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="btn-primary w-full py-3 text-base"
        :disabled="loading"
      >
        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ loading ? 'Entrando...' : 'Entrar no portal' }}
        <svg v-if="!loading" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
      </button>
    </form>

    <!-- Divisor -->
    <div class="relative my-6">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200"></div>
      </div>
      <div class="relative flex justify-center">
        <span class="bg-white px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
          Ambiente de demonstração
        </span>
      </div>
    </div>

    <!-- Credenciais demo -->
    <div class="rounded-xl bg-gradient-to-br from-primary-50 to-white ring-1 ring-primary-100 p-4">
      <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-lg bg-primary-600 flex items-center justify-center text-white shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1 text-sm">
          <p class="font-bold text-primary-800 mb-1">Credenciais de teste</p>
          <div class="space-y-0.5 text-primary-700/90 font-mono text-xs">
            <p><span class="font-sans font-semibold">E-mail:</span> admin@lojacorr.com.br</p>
            <p><span class="font-sans font-semibold">Senha:</span> lojacorr2024</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
