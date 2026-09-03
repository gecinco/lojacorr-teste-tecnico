<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'

export type SelectValue = string | number | null

export interface SelectOption {
  value: string | number
  label: string
}

const props = withDefaults(defineProps<{
  modelValue: SelectValue
  options: SelectOption[]
  placeholder?: string
  error?: string
  disabled?: boolean
  clearable?: boolean
  id?: string
  label?: string
}>(), {
  placeholder: 'Selecione...',
  disabled: false,
  clearable: true,
})

const emit = defineEmits<{
  'update:modelValue': [value: SelectValue]
  blur: []
}>()

const open = ref(false)
const root = ref<HTMLElement | null>(null)
const menuStyle = ref<Record<string, string>>({})

const selectedOption = computed(() =>
  props.options.find(option => option.value === props.modelValue)
)

const hasError = computed(() => Boolean(props.error))

// Throttle por rAF: getBoundingClientRect força reflow a cada evento de scroll.
let positionFrame: number | null = null

const scheduleMenuPosition = () => {
  // Menu fechado não acompanha scroll: recalculado no toggle().
  if (!open.value || positionFrame !== null) return
  positionFrame = requestAnimationFrame(() => {
    positionFrame = null
    updateMenuPosition()
  })
}

const updateMenuPosition = () => {
  if (!root.value) return

  const rect = root.value.getBoundingClientRect()
  const menuHeight = 240
  const spaceBelow = window.innerHeight - rect.bottom
  const openUp = spaceBelow < menuHeight && rect.top > spaceBelow

  menuStyle.value = {
    position: 'fixed',
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: '50',
    ...(openUp
      ? { bottom: `${window.innerHeight - rect.top + 4}px` }
      : { top: `${rect.bottom + 4}px` }),
  }
}

const selectOption = (value: SelectValue) => {
  emit('update:modelValue', value)
  open.value = false
  emit('blur')
}

const toggle = () => {
  if (props.disabled) return
  if (!open.value) updateMenuPosition()
  open.value = !open.value
}

const onClickOutside = (event: MouseEvent) => {
  const target = event.target as Node
  if (root.value?.contains(target)) return
  if ((target as HTMLElement).closest?.('[data-select-menu]')) return
  if (open.value) emit('blur')
  open.value = false
}

onMounted(() => {
  document.addEventListener('mousedown', onClickOutside)
  window.addEventListener('resize', scheduleMenuPosition)
  // passive: handler não chama preventDefault, não bloqueia a rolagem.
  window.addEventListener('scroll', scheduleMenuPosition, { capture: true, passive: true })
})

onUnmounted(() => {
  document.removeEventListener('mousedown', onClickOutside)
  window.removeEventListener('resize', scheduleMenuPosition)
  window.removeEventListener('scroll', scheduleMenuPosition, { capture: true })
  if (positionFrame !== null) cancelAnimationFrame(positionFrame)
})
</script>

<template>
  <div ref="root" class="relative" :data-field-error="hasError ? true : undefined">
    <label v-if="label" class="label" :for="id">{{ label }}</label>
    <button
      :id="id"
      type="button"
      class="input text-left flex items-center justify-between gap-2"
      :class=" [
        hasError && 'input-error',
        !selectedOption && 'text-gray-400',
        open && !hasError && 'ring-2 ring-primary-500 border-primary-500',
      ]"
      :disabled="disabled"
      :aria-expanded="open"
      aria-haspopup="listbox"
      :aria-invalid="hasError ? true : undefined"
      :aria-describedby="hasError && id ? `${id}-error` : undefined"
      @click="toggle"
    >
      <span class="truncate">
        {{ selectedOption?.label || placeholder }}
      </span>
      <svg
        class="w-4 h-4 text-gray-400 shrink-0 transition-transform"
        :class="{ 'rotate-180': open }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <p v-if="error" :id="id ? `${id}-error` : undefined" class="error-text">{{ error }}</p>

    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="open"
          data-select-menu
          role="listbox"
          class="max-h-60 overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
          :style="menuStyle"
        >
          <button
            v-if="clearable"
            type="button"
            class="w-full px-3 py-2 text-left text-sm text-gray-500 hover:bg-gray-50"
            :class="{ 'bg-primary-50 text-primary-700': modelValue === null || modelValue === '' }"
            @click="selectOption(null)"
          >
            {{ placeholder }}
          </button>
          <button
            v-for="option in options"
            :key="String(option.value)"
            type="button"
            role="option"
            class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700"
            :class="{ 'bg-primary-50 text-primary-700 font-medium': modelValue === option.value }"
            @click="selectOption(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
