<script setup lang="ts">
definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

onMounted(() => {
  navigateTo('/seguros')
})
</script>

<template>
  <div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
      <div class="inline-flex flex-col items-center gap-4">
        <div class="relative">
          <div class="absolute inset-0 rounded-full bg-primary-100 animate-ping"></div>
          <div class="relative w-16 h-16 rounded-full bg-gradient-to-br from-primary-600 to-wine-700 flex items-center justify-center shadow-brand-lg">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-white border-t-transparent"></div>
          </div>
        </div>
        <p class="text-sm font-semibold text-primary-700 tracking-wide uppercase">
          Carregando portal…
        </p>
      </div>
    </div>
  </div>
</template>
