import type { ApiResponse, PaginatedResponse } from '~/types'

/** Wrapper fino em torno de `fetch`; métodos aceitam `AbortSignal` opcional. */
export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const baseURL = config.public.apiBase

  const getHeaders = (): HeadersInit => {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    if (authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`
    }

    return headers
  }

  const handleResponse = async <T>(response: Response): Promise<T> => {
    const data = await response.json()

    if (!response.ok) {
      if (response.status === 401) {
        authStore.clearAuth()
        navigateTo('/login')
      }
      throw new ApiError(data.message || 'Erro na requisição', response.status, data.errors)
    }

    return data
  }

  const buildUrl = (endpoint: string, params?: Record<string, any>): string => {
    let url = `${baseURL}${endpoint}`

    if (!params) return url

    const searchParams = new URLSearchParams()
    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined && value !== null && value !== '') {
        searchParams.append(key, String(value))
      }
    }

    const queryString = searchParams.toString()
    return queryString ? `${url}?${queryString}` : url
  }

  const get = async <T>(
    endpoint: string,
    params?: Record<string, any>,
    signal?: AbortSignal,
  ): Promise<T> => {
    const response = await fetch(buildUrl(endpoint, params), {
      method: 'GET',
      headers: getHeaders(),
      signal,
    })

    return handleResponse<T>(response)
  }

  const post = async <T>(endpoint: string, body?: any, signal?: AbortSignal): Promise<T> => {
    const response = await fetch(`${baseURL}${endpoint}`, {
      method: 'POST',
      headers: getHeaders(),
      body: body ? JSON.stringify(body) : undefined,
      signal,
    })

    return handleResponse<T>(response)
  }

  const put = async <T>(endpoint: string, body?: any, signal?: AbortSignal): Promise<T> => {
    const response = await fetch(`${baseURL}${endpoint}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: body ? JSON.stringify(body) : undefined,
      signal,
    })

    return handleResponse<T>(response)
  }

  const del = async <T>(endpoint: string, signal?: AbortSignal): Promise<T> => {
    const response = await fetch(`${baseURL}${endpoint}`, {
      method: 'DELETE',
      headers: getHeaders(),
      signal,
    })

    return handleResponse<T>(response)
  }

  return {
    get,
    post,
    put,
    del,
  }
}

export class ApiError extends Error {
  status: number
  errors?: Record<string, string[]>

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

/** True se o erro veio de um AbortController.abort(). */
export const isAbortError = (error: unknown): boolean => {
  return error instanceof DOMException && error.name === 'AbortError'
}
