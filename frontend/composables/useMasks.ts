/** Máscaras/normalizações de strings — funções puras, sem estado. */

export const maskCpf = (value: string): string => {
  const numeros = value.replace(/\D/g, '').slice(0, 11)
  return numeros
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

export const maskCnpj = (value: string): string => {
  const numeros = value.replace(/\D/g, '').slice(0, 14)
  return numeros
    .replace(/(\d{2})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1/$2')
    .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
}

// CNPJ alfanumérico: a máscara mantém a mesma forma, preservando letras na base.
export const maskCnpjAlfanumerico = (value: string): string => {
  const limpo = value.toUpperCase().replace(/[^0-9A-Z]/g, '').slice(0, 14)
  return limpo
    .replace(/([0-9A-Z]{2})([0-9A-Z])/, '$1.$2')
    .replace(/([0-9A-Z]{3})([0-9A-Z])/, '$1.$2')
    .replace(/([0-9A-Z]{3})([0-9A-Z])/, '$1/$2')
    .replace(/([0-9A-Z]{4})([0-9]{1,2})$/, '$1-$2')
}

export const maskDocumento = (value: string): string => {
  if (/[A-Za-z]/.test(value)) {
    return maskCnpjAlfanumerico(value)
  }
  const numeros = value.replace(/\D/g, '')
  if (numeros.length <= 11) {
    return maskCpf(numeros)
  }
  return maskCnpj(numeros)
}

export const maskCep = (value: string): string => {
  const numeros = value.replace(/\D/g, '').slice(0, 8)
  return numeros.replace(/(\d{5})(\d{1,3})$/, '$1-$2')
}

export const unmaskCurrency = (value: string): number => {
  const digits = value.replace(/\D/g, '')
  if (!digits) return 0
  return parseInt(digits, 10) / 100
}

export const maskCurrency = (value: string | number): string => {
  const numericValue = typeof value === 'string' ? unmaskCurrency(value) : value

  if (isNaN(numericValue)) {
    return 'R$ 0,00'
  }

  return numericValue.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  })
}

export const maskDate = (value: string): string => {
  const numeros = value.replace(/\D/g, '').slice(0, 8)
  return numeros
    .replace(/(\d{2})(\d)/, '$1/$2')
    .replace(/(\d{2})(\d)/, '$1/$2')
}

export const formatDateToISO = (dateString: string): string => {
  const parts = dateString.split('/')
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1]}-${parts[0]}`
  }
  return dateString
}

export const formatDateToBR = (isoString: string): string => {
  if (!isoString) return ''
  const parts = isoString.split('-')
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`
  }
  return isoString
}

export const unmaskDocument = (value: string): string =>
  value.toUpperCase().replace(/[^0-9A-Z]/g, '').slice(0, 14)

export const unmaskCep = (value: string): string => value.replace(/\D/g, '')

export const normalizeDate = (value: string): string => {
  if (!value) return ''
  const match = value.match(/^(\d{1,})-(\d{2})-(\d{2})/)
  if (!match) return value.slice(0, 10)

  const year = match[1].slice(0, 4)
  return `${year}-${match[2]}-${match[3]}`
}

/** Wrapper de compatibilidade — novos usos devem importar as funções direto. */
export function useMasks() {
  return {
    maskCpf,
    maskCnpj,
    maskCnpjAlfanumerico,
    maskDocumento,
    maskCep,
    maskCurrency,
    unmaskCurrency,
    maskDate,
    formatDateToISO,
    formatDateToBR,
    unmaskDocument,
    unmaskCep,
    normalizeDate,
  }
}
