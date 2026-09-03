import { ref } from 'vue'

interface Toast {
  id: number
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration: number
}

const toasts = ref<Toast[]>([])
let toastId = 0

export function useToast() {
  const addToast = (
    message: string,
    type: Toast['type'] = 'info',
    duration: number = 4000
  ) => {
    const id = ++toastId
    const toast: Toast = { id, message, type, duration }
    toasts.value.push(toast)

    setTimeout(() => {
      removeToast(id)
    }, duration)
  }

  const removeToast = (id: number) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  const success = (message: string, duration?: number) => {
    addToast(message, 'success', duration)
  }

  const error = (message: string, duration?: number) => {
    addToast(message, 'error', duration)
  }

  const warning = (message: string, duration?: number) => {
    addToast(message, 'warning', duration)
  }

  const info = (message: string, duration?: number) => {
    addToast(message, 'info', duration)
  }

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
  }
}
