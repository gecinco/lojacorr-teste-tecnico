<script setup lang="ts">
const { toasts, removeToast } = useToast()

const getToastClass = (type: string) => {
  switch (type) {
    case 'success':
      return 'bg-white ring-success-500/30 border-l-4 border-success-500'
    case 'error':
      return 'bg-white ring-danger-500/30 border-l-4 border-danger-500'
    case 'warning':
      return 'bg-white ring-warning-500/30 border-l-4 border-warning-500'
    default:
      return 'bg-white ring-primary-500/30 border-l-4 border-primary-500'
  }
}

const getToastIconWrap = (type: string) => {
  switch (type) {
    case 'success':
      return 'bg-success-100 text-success-700'
    case 'error':
      return 'bg-danger-100 text-danger-700'
    case 'warning':
      return 'bg-warning-100 text-warning-700'
    default:
      return 'bg-primary-100 text-primary-700'
  }
}

const getToastIcon = (type: string) => {
  switch (type) {
    case 'success':
      return 'M5 13l4 4L19 7'
    case 'error':
      return 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    case 'warning':
      return 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    default:
      return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
  }
}

const getToastLabel = (type: string) => {
  switch (type) {
    case 'success':
      return 'Sucesso'
    case 'error':
      return 'Erro'
    case 'warning':
      return 'Atenção'
    default:
      return 'Informação'
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-md w-[calc(100%-2rem)] sm:w-auto">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            getToastClass(toast.type),
            'flex items-start gap-3 pl-4 pr-3 py-3 rounded-xl shadow-brand ring-1 min-w-[280px] sm:min-w-[340px]'
          ]"
          role="alert"
        >
          <span
            :class="[getToastIconWrap(toast.type), 'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="getToastIcon(toast.type)" />
            </svg>
          </span>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ getToastLabel(toast.type) }}</p>
            <p class="text-sm font-medium text-gray-800 break-words">{{ toast.message }}</p>
          </div>
          <button
            @click="removeToast(toast.id)"
            class="text-gray-400 hover:text-gray-700 transition-colors flex-shrink-0 mt-0.5"
            aria-label="Fechar notificação"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
