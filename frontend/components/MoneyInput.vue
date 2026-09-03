<script setup lang="ts">
import { computed } from 'vue'
import { useMasks } from '~/composables/useMasks'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  modelValue: number | null
  label?: string
  error?: string
  placeholder?: string
  id?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
  blur: []
}>()

const masks = useMasks()

const displayValue = computed({
  get: () => (props.modelValue ? masks.maskCurrency(props.modelValue) : ''),
  set: (value: string) => {
    const digits = value.replace(/\D/g, '')
    emit('update:modelValue', digits ? masks.unmaskCurrency(value) : null)
  },
})
</script>

<template>
  <div :data-field-error="error ? true : undefined">
    <label v-if="label" class="label" :for="id">{{ label }}</label>
    <input
      :id="id"
      v-model="displayValue"
      type="text"
      inputmode="numeric"
      class="input"
      :class="{ 'input-error': error }"
      :placeholder="placeholder || 'R$ 0,00'"
      :aria-invalid="error ? true : undefined"
      :aria-describedby="error && id ? `${id}-error` : undefined"
      v-bind="$attrs"
      @blur="emit('blur')"
    />
    <p v-if="error" :id="id ? `${id}-error` : undefined" class="error-text">{{ error }}</p>
  </div>
</template>
