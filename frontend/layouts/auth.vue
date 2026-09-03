<template>
  <div class="min-h-screen brand-hero relative overflow-hidden flex items-center justify-center px-4 py-10">
    <!-- Ornamentos de fundo -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
      <div class="absolute -top-32 -right-32 w-[420px] h-[420px] rounded-full bg-accent-500/20 blur-3xl"></div>
      <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] rounded-full bg-primary-600/25 blur-3xl"></div>
      <svg class="absolute right-6 bottom-6 w-72 h-72 text-white/5" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="100" cy="100" r="30"/>
        <circle cx="100" cy="100" r="50"/>
        <circle cx="100" cy="100" r="70"/>
        <circle cx="100" cy="100" r="90"/>
      </svg>
    </div>

    <div class="relative w-full max-w-5xl grid lg:grid-cols-2 gap-10 items-center">
      <!-- Lado esquerdo: apresentação (desktop) -->
      <div class="hidden lg:block text-white space-y-8">
        <BrandLogo variant="light" size="xl" />

        <div class="space-y-4">
          <h1 class="font-display text-4xl xl:text-5xl font-extrabold leading-tight">
            Proteção que
            <span class="block bg-gradient-to-r from-white to-accent-200 bg-clip-text text-transparent">
              cabe no seu dia.
            </span>
          </h1>
          <p class="text-white/80 text-lg max-w-md">
            Contrate seguros e acompanhe vigências, parcelas e status de
            cobertura da sua carteira, com segurança e auditoria.
          </p>
        </div>

        <ul class="space-y-3 text-white/90">
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/10 ring-1 ring-white/20 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </span>
            <span class="font-medium">Contratação em minutos, sem burocracia</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/10 ring-1 ring-white/20 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </span>
            <span class="font-medium">Dados protegidos e auditados</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/10 ring-1 ring-white/20 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </span>
            <span class="font-medium">Integração com seguradoras parceiras</span>
          </li>
        </ul>

        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
          <div class="brand-chip">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
            Emissão rápida
          </div>
          <div class="brand-chip">Auditoria em tempo real</div>
        </div>
      </div>

      <!-- Lado direito: card de login -->
      <div class="w-full max-w-md mx-auto lg:mx-0 lg:justify-self-end">
        <div class="lg:hidden flex justify-center mb-6">
          <BrandLogo variant="light" size="lg" />
        </div>

        <div class="relative">
          <!-- Barra superior de destaque -->
          <div class="h-2 rounded-t-2xl bg-gradient-to-r from-accent-600 via-primary-600 to-wine-700"></div>
          <div class="bg-white rounded-b-2xl shadow-brand-lg ring-1 ring-black/5">
            <slot />
          </div>
          <p class="text-center text-white/70 text-xs mt-6">
            © {{ new Date().getFullYear() }} Portal Seguros. Todos os direitos reservados
          </p>
        </div>
      </div>
    </div>

    <!-- Toast Container também disponível no fluxo de auth -->
    <ToastContainer />
  </div>
</template>
