// Tipos de Usuário
export interface User {
  id: number
  name: string
  email: string
  created_at: string
}

// Tipos de Autenticação
export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthResponse {
  user: User
  token: string
  token_type: string
  expires_in: number
}

// Tipos de Seguradora e Ramo
export interface Seguradora {
  id: number
  nome: string
  codigo: string
}

export interface Ramo {
  id: number
  nome: string
  codigo: string
}

// Tipos de Endereço
export interface Endereco {
  cep: string
  cep_formatado: string
  logradouro: string
  numero: string | null
  complemento: string | null
  bairro: string
  cidade: string
  uf: string
  endereco_completo: string
}

// Tipos de Seguro
export interface Seguro {
  id: number
  documento_segurado: string
  documento_formatado: string
  tipo_documento: 'cpf' | 'cnpj'
  nome_segurado: string
  seguradora: Seguradora
  ramo: Ramo
  valor_total: number
  valor_total_formatado: string
  quantidade_parcelas: number
  valor_parcela: number
  valor_parcela_formatado: string
  inicio_vigencia: string
  inicio_vigencia_formatado: string
  fim_vigencia: string
  fim_vigencia_formatado: string
  status_vigencia: 'vigente' | 'vencido' | 'a_vencer'
  endereco: Endereco
  created_at: string
  updated_at: string
}

export interface SeguroFormData {
  documento_segurado: string
  nome_segurado: string
  seguradora_id: number | null
  ramo_id: number | null
  valor_total: number | null
  quantidade_parcelas: number
  valor_parcela: number | null
  inicio_vigencia: string
  fim_vigencia: string
  cep: string
  logradouro: string
  numero: string
  complemento: string
  bairro: string
  cidade: string
  uf: string
}

// Tipos de Filtros de Listagem
export type SeguroStatus = 'vigente' | 'a_vencer' | 'vencido'

export interface SeguroFilters {
  documento?: string
  status?: SeguroStatus | null
  inicio_vigencia_de?: string
  inicio_vigencia_ate?: string
  fim_vigencia_de?: string
  fim_vigencia_ate?: string
  seguradora_id?: number | null
  ramo_id?: number | null
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: number
}

// Tipos de Resposta da API
export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export interface PaginatedResponse<T> {
  success: boolean
  message: string
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

// Tipos de CEP
export interface CepResponse {
  cep: string
  logradouro: string
  complemento: string
  bairro: string
  cidade: string
  uf: string
}

// Tipos de Validação
export interface ValidationErrors {
  [key: string]: string[]
}
