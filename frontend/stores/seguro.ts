import { defineStore } from 'pinia'
import type { Seguro, SeguroFilters, SeguroFormData, PaginatedResponse, ApiResponse } from '~/types'
import { isAbortError } from '~/composables/useApi'
import { unmaskDocument, unmaskCep } from '~/composables/useMasks'

interface SeguroSummary {
  total: number
  vigente: number
  a_vencer: number
  vencido: number
}

interface SeguroState {
  seguros: Seguro[]
  currentSeguro: Seguro | null
  loading: boolean
  error: string | null
  filters: SeguroFilters
  pagination: {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
  }
  summary: SeguroSummary
}

// AbortControllers fora do state: valores não-reativos por natureza.
let fetchSegurosAbort: AbortController | null = null
let summaryAbort: AbortController | null = null

export const useSeguroStore = defineStore('seguro', {
  state: (): SeguroState => ({
    seguros: [],
    currentSeguro: null,
    loading: false,
    error: null,
    filters: {
      sort_by: 'created_at',
      sort_order: 'desc',
      per_page: 10,
      page: 1,
    },
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 10,
      total: 0,
    },
    summary: { total: 0, vigente: 0, a_vencer: 0, vencido: 0 },
  }),

  getters: {
    getSeguros: (state) => state.seguros,
    getCurrentSeguro: (state) => state.currentSeguro,
    isLoading: (state) => state.loading,
    getFilters: (state) => state.filters,
    getPagination: (state) => state.pagination,
    getSummary: (state) => state.summary,
  },

  actions: {
    async fetchSeguros(customFilters?: Partial<SeguroFilters>): Promise<void> {
      // Cancela a requisição anterior para a resposta nova não ser sobrescrita.
      fetchSegurosAbort?.abort()
      fetchSegurosAbort = new AbortController()
      const controller = fetchSegurosAbort

      this.loading = true
      this.error = null
      const api = useApi()

      try {
        const params = { ...this.filters, ...customFilters }
        const response = await api.get<PaginatedResponse<Seguro>>(
          '/seguros',
          params,
          controller.signal,
        )

        if (response.success) {
          this.seguros = response.data
          this.pagination = {
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            perPage: response.meta.per_page,
            total: response.meta.total,
          }
        }
      } catch (error) {
        if (isAbortError(error)) return
        console.error('Erro ao buscar seguros:', error)
        this.error = 'Não foi possível carregar os seguros. Tente novamente.'
        this.seguros = []
      } finally {
        if (fetchSegurosAbort === controller) {
          this.loading = false
          fetchSegurosAbort = null
        }
      }
    },

    async fetchSummary(): Promise<void> {
      summaryAbort?.abort()
      summaryAbort = new AbortController()
      const controller = summaryAbort

      const api = useApi()

      try {
        const response = await api.get<ApiResponse<SeguroSummary>>(
          '/seguros/summary',
          undefined,
          controller.signal,
        )
        if (response.success && response.data) {
          this.summary = response.data
        }
      } catch (error) {
        if (isAbortError(error)) return
        console.error('Erro ao buscar resumo de seguros:', error)
      } finally {
        if (summaryAbort === controller) {
          summaryAbort = null
        }
      }
    },

    async fetchSeguro(id: number): Promise<Seguro | null> {
      this.loading = true
      const api = useApi()

      try {
        const response = await api.get<ApiResponse<Seguro>>(`/seguros/${id}`)
        if (response.success && response.data) {
          this.currentSeguro = response.data
          return response.data
        }
        return null
      } catch (error) {
        console.error('Erro ao buscar seguro:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createSeguro(data: SeguroFormData): Promise<Seguro> {
      this.loading = true
      const api = useApi()

      try {
        const payload = {
          ...data,
          documento_segurado: unmaskDocument(data.documento_segurado),
          cep: unmaskCep(data.cep),
        }

        const response = await api.post<ApiResponse<Seguro>>('/seguros', payload)

        if (response.success && response.data) {
          return response.data
        }

        throw new Error('Falha ao criar seguro')
      } catch (error) {
        console.error('Erro ao criar seguro:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateSeguro(id: number, data: SeguroFormData): Promise<Seguro> {
      this.loading = true
      const api = useApi()

      try {
        const payload = {
          ...data,
          documento_segurado: unmaskDocument(data.documento_segurado),
          cep: unmaskCep(data.cep),
        }

        const response = await api.put<ApiResponse<Seguro>>(`/seguros/${id}`, payload)

        if (response.success && response.data) {
          return response.data
        }

        throw new Error('Falha ao atualizar seguro')
      } catch (error) {
        console.error('Erro ao atualizar seguro:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteSeguro(id: number): Promise<void> {
      this.loading = true
      const api = useApi()

      try {
        await api.del(`/seguros/${id}`)
        await Promise.all([this.fetchSeguros(), this.fetchSummary()])
      } catch (error) {
        console.error('Erro ao deletar seguro:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    setFilters(filters: Partial<SeguroFilters>): void {
      this.filters = { ...this.filters, ...filters }
    },

    resetFilters(): void {
      this.filters = {
        sort_by: 'created_at',
        sort_order: 'desc',
        per_page: 10,
        page: 1,
      }
    },

    setPage(page: number): void {
      this.filters.page = page
    },

    setSorting(sortBy: string, sortOrder: 'asc' | 'desc'): void {
      this.filters.sort_by = sortBy
      this.filters.sort_order = sortOrder
    },
  },
})
