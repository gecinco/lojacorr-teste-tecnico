export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) {
    return
  }

  const authStore = useAuthStore()
  authStore.initAuth()

  const isLogin = to.path === '/login'

  if (!authStore.isAuthenticated && !isLogin) {
    return navigateTo('/login')
  }

  if (authStore.isAuthenticated && isLogin) {
    return navigateTo('/seguros')
  }
})
