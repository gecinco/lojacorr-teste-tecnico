<script setup lang="ts">
import { computed } from 'vue'
import { useMasks } from '~/composables/useMasks'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  modelValue: string
  label?: string
  error?: string
  id?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  blur: []
}>()

const masks = useMasks()

const tipoDocumento = computed(() => {
  const doc = props.modelValue.replace(/\D/g, '')
  return doc.length <= 11 ? 'cpf' : 'cnpj'
})

const displayValue = computed({
  get: () => masks.maskDocumento(props.modelValue),
  set: (value: string) => emit('update:modelValue', value),
})
</script>

<template>
  <div :data-field-error="error ? true : undefined">
    <label v-if="label" class="label" :for="id">
      {{ label }}
      <span class="text-xs text-gray-500 ml-1">({{ tipoDocumento.toUpperCase() }})</span>
    </label>
    <input
      :id="id"
      v-model="displayValue"
      type="text"
      class="input font-mono"
      :class="{ 'input-error': error }"
      :placeholder="tipoDocumento === 'cpf' ? '000.000.000-00' : '00.000.000/0000-00'"
      maxlength="18"
      :aria-invalid="error ? true : undefined"
      :aria-describedby="error && id ? `${id}-error` : undefined"
      v-bind="$attrs"
      @blur="emit('blur')"
    />
    <p v-if="error" :id="id ? `${id}-error` : undefined" class="error-text">{{ error }}</p>
  </div>
</template>
