<script setup lang="ts">
withDefaults(defineProps<{
  variant?: 'light' | 'dark'
  showText?: boolean
  size?: 'sm' | 'md' | 'lg' | 'xl'
}>(), {
  variant: 'dark',
  showText: true,
  size: 'md',
})

const sizeMap = {
  sm: { mark: 'w-6 h-6', text: 'text-base', tag: 'text-[9px]' },
  md: { mark: 'w-8 h-8', text: 'text-xl', tag: 'text-[10px]' },
  lg: { mark: 'w-10 h-10', text: 'text-2xl', tag: 'text-xs' },
  xl: { mark: 'w-14 h-14', text: 'text-4xl', tag: 'text-sm' },
} as const
</script>

<template>
  <div class="inline-flex items-center gap-2.5 select-none">
    <!-- Escudo minimalista com checkmark (marca original) -->
    <svg
      :class="sizeMap[size].mark"
      viewBox="0 0 48 48"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <defs>
        <linearGradient :id="`brand-grad-${variant}-${size}`" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" :stop-color="variant === 'light' ? '#ffffff' : '#c8102e'" />
          <stop offset="100%" :stop-color="variant === 'light' ? '#fbe7ea' : '#7b1e2a'" />
        </linearGradient>
      </defs>
      <!-- Escudo -->
      <path
        d="M24 4 L42 10 V24 C42 34 34 42 24 44 C14 42 6 34 6 24 V10 Z"
        :fill="`url(#brand-grad-${variant}-${size})`"
      />
      <!-- Checkmark interno -->
      <path
        d="M15 24 L21 30 L33 18"
        :stroke="variant === 'light' ? '#7b1e2a' : '#ffffff'"
        stroke-width="4"
        stroke-linecap="round"
        stroke-linejoin="round"
        fill="none"
      />
    </svg>

    <div v-if="showText" class="flex flex-col leading-none">
      <span
        :class="[
          sizeMap[size].text,
          'font-display font-extrabold tracking-tight',
          variant === 'light' ? 'text-white' : 'text-primary-700',
        ]"
      >
        Seguros
      </span>
      <span
        :class="[
          sizeMap[size].tag,
          'uppercase font-semibold tracking-[0.22em] mt-0.5',
          variant === 'light' ? 'text-white/70' : 'text-primary-500/80',
        ]"
      >
        Portal de Seguros
      </span>
    </div>
  </div>
</template>
