import { describe, it, expect } from 'vitest'
import { useValidation } from '../../composables/useValidation'

const {
  validateCpf,
  validateCnpj,
  validateDocumento,
  validateCoerenciaFinanceira,
  validateVigencia,
} = useValidation()

describe('useValidation', () => {
  describe('validateCpf', () => {
    it('valida CPF correto', () => {
      expect(validateCpf('12345678909').valid).toBe(true)
      expect(validateCpf('111.444.777-35').valid).toBe(true)
    })

    it('rejeita CPF com dígitos verificadores incorretos', () => {
      expect(validateCpf('12345678900').valid).toBe(false)
      expect(validateCpf('11144477700').valid).toBe(false)
    })

    it('rejeita CPF com sequência repetida', () => {
      expect(validateCpf('11111111111').valid).toBe(false)
      expect(validateCpf('00000000000').valid).toBe(false)
      expect(validateCpf('99999999999').valid).toBe(false)
    })

    it('rejeita CPF com tamanho incorreto', () => {
      expect(validateCpf('123456789').valid).toBe(false)
      expect(validateCpf('1234567890123').valid).toBe(false)
    })

    it('limpa caracteres não numéricos antes de validar', () => {
      expect(validateCpf('123.456.789-09').valid).toBe(true)
    })
  })

  describe('validateCnpj', () => {
    it('valida CNPJ correto', () => {
      expect(validateCnpj('11222333000181').valid).toBe(true)
      expect(validateCnpj('11.222.333/0001-81').valid).toBe(true)
    })

    it('rejeita CNPJ com dígitos verificadores incorretos', () => {
      expect(validateCnpj('11222333000100').valid).toBe(false)
    })

    it('rejeita CNPJ com sequência repetida', () => {
      expect(validateCnpj('11111111111111').valid).toBe(false)
      expect(validateCnpj('00000000000000').valid).toBe(false)
    })

    it('rejeita CNPJ com tamanho incorreto', () => {
      expect(validateCnpj('1122233300018').valid).toBe(false)
      expect(validateCnpj('112223330001811').valid).toBe(false)
    })
  })

  describe('validateDocumento', () => {
    it('identifica CPF e CNPJ pelo tamanho', () => {
      expect(validateDocumento('12345678909').tipo).toBe('cpf')
      expect(validateDocumento('11222333000181').tipo).toBe('cnpj')
    })

    it('rejeita documento vazio', () => {
      expect(validateDocumento('').valid).toBe(false)
    })
  })

  describe('validateCoerenciaFinanceira', () => {
    it('valida quando valor parcela * quantidade = valor total', () => {
      expect(validateCoerenciaFinanceira(1200, 12, 100).valid).toBe(true)
    })

    it('valida com tolerância de R$ 0,01 para arredondamento', () => {
      expect(validateCoerenciaFinanceira(100, 3, 33.33).valid).toBe(true)
    })

    it('rejeita quando diferença excede tolerância', () => {
      expect(validateCoerenciaFinanceira(1200, 12, 90).valid).toBe(false)
      expect(validateCoerenciaFinanceira(1200, 12, 110).valid).toBe(false)
    })

    it('rejeita quando quantidade de parcelas é zero', () => {
      expect(validateCoerenciaFinanceira(1200, 0, 100).valid).toBe(false)
    })

    it('funciona com parcela única', () => {
      expect(validateCoerenciaFinanceira(5000, 1, 5000).valid).toBe(true)
    })
  })

  describe('validateVigencia', () => {
    it('valida quando o fim é posterior ao início', () => {
      expect(validateVigencia('2026-01-01', '2027-01-01').valid).toBe(true)
    })

    it('rejeita quando o fim não é posterior ao início', () => {
      expect(validateVigencia('2026-01-01', '2026-01-01').valid).toBe(false)
      expect(validateVigencia('2026-02-01', '2026-01-01').valid).toBe(false)
    })

    it('rejeita datas vazias', () => {
      expect(validateVigencia('', '2026-01-01').valid).toBe(false)
    })
  })
})
