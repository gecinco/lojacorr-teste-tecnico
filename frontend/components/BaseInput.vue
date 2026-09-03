<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
  modelValue?: string | number | null
  label?: string
  error?: string
  hint?: string
  id?: string
  type?: string
}>(), {
  modelValue: '',
  type: 'text',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  blur: []
}>()

const masks = useMasks()

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value

  if (props.type === 'date') {
    value = masks.normalizeDate(value)
    target.value = value
  }

  emit('update:modelValue', value)
}
</script>

<template>
  <div :data-field-error="error ? true : undefined">
    <label v-if="label" class="label" :for="id">
      <slot name="label">{{ label }}</slot>
    </label>
    <div class="relative">
      <input
        :id="id"
        :type="type"
        class="input"
        :class="{ 'input-error': error }"
        :value="modelValue ?? ''"
        :min="type === 'date' ? '1900-01-01' : undefined"
        :max="type === 'date' ? '2100-12-31' : undefined"
        :aria-invalid="error ? true : undefined"
        :aria-describedby="error && id ? `${id}-error` : undefined"
        v-bind="$attrs"
        @input="onInput"
        @blur="emit('blur')"
      />
      <slot name="suffix" />
    </div>
    <p v-if="error" :id="id ? `${id}-error` : undefined" class="error-text">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-sm text-warning-600">{{ hint }}</p>
  </div>
</template>
