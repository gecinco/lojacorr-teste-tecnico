import { defineStore } from 'pinia'
import type { Seguradora, Ramo, ApiResponse, CepResponse } from '~/types'

interface DataState {
  seguradoras: Seguradora[]
  ramos: Ramo[]
  loadingSeguradoras: boolean
  loadingRamos: boolean
  loadingCep: boolean
}

export const useDataStore = defineStore('data', {
  state: (): DataState => ({
    seguradoras: [],
    ramos: [],
    loadingSeguradoras: false,
    loadingRamos: false,
    loadingCep: false,
  }),

  getters: {
    getSeguradoras: (state) => state.seguradoras,
    getRamos: (state) => state.ramos,
  },

  actions: {
    async fetchSeguradoras(): Promise<void> {
      if (this.seguradoras.length > 0) return

      this.loadingSeguradoras = true
      const api = useApi()

      try {
        const response = await api.get<ApiResponse<Seguradora[]>>('/seguradoras')
        if (response.success && response.data) {
          this.seguradoras = response.data
        }
      } catch (error) {
        console.error('Erro ao buscar seguradoras:', error)
        throw error
      } finally {
        this.loadingSeguradoras = false
      }
    },

    async fetchRamos(): Promise<void> {
      if (this.ramos.length > 0) return

      this.loadingRamos = true
      const api = useApi()

      try {
        const response = await api.get<ApiResponse<Ramo[]>>('/ramos')
        if (response.success && response.data) {
          this.ramos = response.data
        }
      } catch (error) {
        console.error('Erro ao buscar ramos:', error)
        throw error
      } finally {
        this.loadingRamos = false
      }
    },

    async fetchEnderecoByCep(cep: string): Promise<CepResponse | null> {
      const cepLimpo = cep.replace(/\D/g, '')
      
      if (cepLimpo.length !== 8) {
        return null
      }

      this.loadingCep = true
      const api = useApi()

      try {
        const response = await api.get<ApiResponse<CepResponse>>(`/cep/${cepLimpo}`)
        if (response.success && response.data) {
          return response.data
        }
        return null
      } catch (error) {
        console.error('Erro ao buscar CEP:', error)
        return null
      } finally {
        this.loadingCep = false
      }
    },

    async fetchAllData(): Promise<void> {
      await Promise.all([
        this.fetchSeguradoras(),
        this.fetchRamos(),
      ])
    },
  },
})
