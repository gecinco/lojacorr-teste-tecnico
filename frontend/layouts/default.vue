<script setup lang="ts">
const authStore = useAuthStore()
const userMenuOpen = ref(false)
const mobileMenuOpen = ref(false)

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login', { replace: true })
}

const userInitial = computed(() =>
  authStore.user?.name?.charAt(0).toUpperCase() ?? 'U',
)

const closeUserMenu = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('[data-user-menu]')) userMenuOpen.value = false
}

onMounted(() => document.addEventListener('click', closeUserMenu))
onUnmounted(() => document.removeEventListener('click', closeUserMenu))
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Faixa fina superior -->
    <div class="h-1 w-full bg-gradient-to-r from-accent-600 via-primary-600 to-wine-700"></div>

    <!-- Header principal bordô -->
    <header class="brand-topbar sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <NuxtLink to="/seguros" class="flex items-center">
            <BrandLogo variant="light" size="md" />
          </NuxtLink>

          <!-- Navegação desktop -->
          <nav class="hidden md:flex items-center gap-1">
            <NuxtLink to="/seguros" class="nav-link inline-flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
              </svg>
              Meus Seguros
            </NuxtLink>
            <NuxtLink to="/seguros/novo" class="nav-link inline-flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Nova Contratação
            </NuxtLink>
          </nav>

          <!-- User + mobile toggle -->
          <div class="flex items-center gap-2">
            <!-- Menu do usuário -->
            <div class="relative" data-user-menu>
              <button
                type="button"
                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white/10 transition-colors"
                :aria-expanded="userMenuOpen"
                @click.stop="userMenuOpen = !userMenuOpen"
              >
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-accent-500 to-primary-700 ring-2 ring-white/30 flex items-center justify-center text-white font-bold text-sm shadow-inner">
                  {{ userInitial }}
                </div>
                <div class="hidden sm:flex flex-col items-start leading-tight">
                  <span class="text-white text-sm font-semibold">{{ authStore.user?.name }}</span>
                  <span class="text-white/70 text-[10px] uppercase tracking-wider font-semibold">Corretor</span>
                </div>
                <svg class="hidden sm:block w-4 h-4 text-white/80 transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <Transition name="fade">
                <div
                  v-if="userMenuOpen"
                  class="absolute right-0 mt-2 w-64 rounded-xl bg-white shadow-brand ring-1 ring-black/5 overflow-hidden"
                >
                  <div class="px-4 py-3 bg-gradient-to-r from-primary-50 to-white border-b border-primary-100">
                    <p class="text-sm font-bold text-primary-800">{{ authStore.user?.name }}</p>
                    <p class="text-xs text-primary-600 truncate">{{ authStore.user?.email }}</p>
                  </div>
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-700 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sair da conta
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Toggle menu mobile -->
            <button
              type="button"
              class="md:hidden p-2 rounded-lg text-white hover:bg-white/10 transition-colors"
              @click="mobileMenuOpen = !mobileMenuOpen"
              aria-label="Menu"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Nav mobile -->
        <Transition name="slide">
          <nav v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-1">
            <NuxtLink to="/seguros" class="nav-link block" @click="mobileMenuOpen = false">
              Meus Seguros
            </NuxtLink>
            <NuxtLink to="/seguros/novo" class="nav-link block" @click="mobileMenuOpen = false">
              Nova Contratação
            </NuxtLink>
          </nav>
        </Transition>
      </div>
    </header>

    <!-- Conteúdo -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>

    <!-- Rodapé sutil -->
    <footer class="mt-8 border-t border-primary-100 bg-white/70 backdrop-blur">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
        <div class="flex items-center gap-2">
          <BrandLogo variant="dark" size="sm" :show-text="false" />
          <span class="font-semibold text-primary-800">Portal Seguros</span>
          <span class="hidden sm:inline text-gray-400">— Plataforma para corretores.</span>
        </div>
        <div>© {{ new Date().getFullYear() }} • Todos os direitos reservados</div>
      </div>
    </footer>

    <!-- Toast Container -->
    <ToastContainer />
  </div>
</template>
