/** Validações de CPF/CNPJ/coerência financeira/vigência — funções puras. */

type ValidationResult = { valid: boolean; message: string }
type DocumentValidation = ValidationResult & { tipo: 'cpf' | 'cnpj' | null }

const formatCurrency = (value: number): string =>
  value.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })

export const validateCpf = (cpf: string): ValidationResult => {
  const cpfLimpo = cpf.replace(/\D/g, '')

  if (cpfLimpo.length !== 11) {
    return { valid: false, message: 'CPF deve conter 11 dígitos' }
  }

  if (/^(\d)\1{10}$/.test(cpfLimpo)) {
    return { valid: false, message: 'CPF inválido: sequência repetida' }
  }

  let soma = 0
  for (let i = 0; i < 9; i++) {
    soma += parseInt(cpfLimpo.charAt(i)) * (10 - i)
  }
  let resto = (soma * 10) % 11
  if (resto === 10 || resto === 11) resto = 0
  if (resto !== parseInt(cpfLimpo.charAt(9))) {
    return { valid: false, message: 'CPF inválido: dígito verificador incorreto' }
  }

  soma = 0
  for (let i = 0; i < 10; i++) {
    soma += parseInt(cpfLimpo.charAt(i)) * (11 - i)
  }
  resto = (soma * 10) % 11
  if (resto === 10 || resto === 11) resto = 0
  if (resto !== parseInt(cpfLimpo.charAt(10))) {
    return { valid: false, message: 'CPF inválido: dígito verificador incorreto' }
  }

  return { valid: true, message: '' }
}

export const validateCnpj = (cnpj: string): ValidationResult => {
  const cnpjLimpo = cnpj.replace(/\D/g, '')

  if (cnpjLimpo.length !== 14) {
    return { valid: false, message: 'CNPJ deve conter 14 dígitos' }
  }

  if (/^(\d)\1{13}$/.test(cnpjLimpo)) {
    return { valid: false, message: 'CNPJ inválido: sequência repetida' }
  }

  let tamanho = cnpjLimpo.length - 2
  let numeros = cnpjLimpo.substring(0, tamanho)
  const digitos = cnpjLimpo.substring(tamanho)
  let soma = 0
  let pos = tamanho - 7

  for (let i = tamanho; i >= 1; i--) {
    soma += parseInt(numeros.charAt(tamanho - i)) * pos--
    if (pos < 2) pos = 9
  }

  let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
  if (resultado !== parseInt(digitos.charAt(0))) {
    return { valid: false, message: 'CNPJ inválido: primeiro dígito verificador incorreto' }
  }

  tamanho = tamanho + 1
  numeros = cnpjLimpo.substring(0, tamanho)
  soma = 0
  pos = tamanho - 7

  for (let i = tamanho; i >= 1; i--) {
    soma += parseInt(numeros.charAt(tamanho - i)) * pos--
    if (pos < 2) pos = 9
  }

  resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
  if (resultado !== parseInt(digitos.charAt(1))) {
    return { valid: false, message: 'CNPJ inválido: segundo dígito verificador incorreto' }
  }

  return { valid: true, message: '' }
}

export const validateDocumento = (documento: string): DocumentValidation => {
  const documentoLimpo = documento.replace(/\D/g, '')

  if (documentoLimpo.length === 0) {
    return { valid: false, message: 'Documento é obrigatório', tipo: null }
  }

  if (documentoLimpo.length <= 11) {
    return { ...validateCpf(documentoLimpo), tipo: 'cpf' }
  }

  return { ...validateCnpj(documentoLimpo), tipo: 'cnpj' }
}

export const validateCoerenciaFinanceira = (
  valorTotal: number,
  quantidadeParcelas: number,
  valorParcela: number,
): ValidationResult => {
  if (quantidadeParcelas <= 0) {
    return { valid: false, message: 'Quantidade de parcelas deve ser maior que zero' }
  }

  const valorCalculadoCentavos = Math.round(valorParcela * quantidadeParcelas * 100)
  const valorTotalCentavos = Math.round(valorTotal * 100)
  const diferencaCentavos = Math.abs(valorCalculadoCentavos - valorTotalCentavos)

  // Tolerância: 1 centavo por parcela (arredondamento contábil padrão)
  const toleranciaCentavos = quantidadeParcelas

  if (diferencaCentavos > toleranciaCentavos) {
    const valorEsperadoCentavos = Math.round(valorTotalCentavos / quantidadeParcelas)
    const valorEsperado = valorEsperadoCentavos / 100
    return {
      valid: false,
      message: `O valor da parcela não confere. Valor esperado: R$ ${formatCurrency(valorEsperado)}`,
    }
  }

  return { valid: true, message: '' }
}

export const validateVigencia = (inicio: string, fim: string): ValidationResult => {
  if (!inicio || !fim) {
    return { valid: false, message: 'Datas de vigência são obrigatórias' }
  }

  const dataInicio = new Date(inicio)
  const dataFim = new Date(fim)

  if (dataFim <= dataInicio) {
    return { valid: false, message: 'Data de fim deve ser posterior à data de início' }
  }

  return { valid: true, message: '' }
}

/** Wrapper de compatibilidade — novos usos devem importar as funções direto. */
export function useValidation() {
  return {
    validateCpf,
    validateCnpj,
    validateDocumento,
    validateCoerenciaFinanceira,
    validateVigencia,
  }
}
