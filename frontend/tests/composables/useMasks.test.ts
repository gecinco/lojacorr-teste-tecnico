import { describe, it, expect } from 'vitest'
import { useMasks } from '../../composables/useMasks'

const {
  maskCpf,
  maskCnpj,
  maskCnpjAlfanumerico,
  maskDocumento,
  maskCep,
  maskCurrency,
  unmaskCurrency,
} = useMasks()

const asBrl = (value: string) => value.replace(/\u00a0/g, ' ')

describe('useMasks', () => {
  describe('maskCpf', () => {
    it('formata CPF corretamente', () => {
      expect(maskCpf('12345678909')).toBe('123.456.789-09')
    })

    it('formata CPF parcial', () => {
      expect(maskCpf('123')).toBe('123')
      expect(maskCpf('1234')).toBe('123.4')
      expect(maskCpf('1234567')).toBe('123.456.7')
    })

    it('limita a 11 dígitos', () => {
      expect(maskCpf('123456789012345')).toBe('123.456.789-01')
    })
  })

  describe('maskCnpj', () => {
    it('formata CNPJ corretamente', () => {
      expect(maskCnpj('11222333000181')).toBe('11.222.333/0001-81')
    })

    it('formata CNPJ parcial', () => {
      expect(maskCnpj('11')).toBe('11')
      expect(maskCnpj('112')).toBe('11.2')
      expect(maskCnpj('11222333')).toBe('11.222.333')
    })
  })

  describe('maskDocumento', () => {
    it('detecta e formata CPF automaticamente', () => {
      expect(maskDocumento('12345678909')).toBe('123.456.789-09')
    })

    it('detecta e formata CNPJ automaticamente', () => {
      expect(maskDocumento('11222333000181')).toBe('11.222.333/0001-81')
    })

    it('trata entrada com caracteres especiais', () => {
      expect(maskDocumento('123.456.789-09')).toBe('123.456.789-09')
    })

    it('aplica máscara de CNPJ alfanumérico preservando letras', () => {
      expect(maskDocumento('12ABC34501DE35')).toBe('12.ABC.345/01DE-35')
      expect(maskCnpjAlfanumerico('12abc34501de35')).toBe('12.ABC.345/01DE-35')
    })
  })

  describe('maskCep', () => {
    it('formata CEP corretamente', () => {
      expect(maskCep('01310100')).toBe('01310-100')
    })

    it('formata CEP parcial', () => {
      expect(maskCep('01310')).toBe('01310')
      expect(maskCep('013101')).toBe('01310-1')
    })

    it('limita a 8 dígitos', () => {
      expect(maskCep('0131010012345')).toBe('01310-100')
    })
  })

  describe('maskCurrency', () => {
    it('formata valor monetário corretamente', () => {
      expect(asBrl(maskCurrency(1234.56))).toBe('R$ 1.234,56')
    })

    it('formata valores grandes', () => {
      expect(asBrl(maskCurrency(1234567.89))).toBe('R$ 1.234.567,89')
    })

    it('formata valores pequenos', () => {
      expect(asBrl(maskCurrency(0.01))).toBe('R$ 0,01')
    })

    it('trata NaN', () => {
      expect(asBrl(maskCurrency(NaN))).toBe('R$ 0,00')
    })
  })

  describe('unmaskCurrency', () => {
    it('remove formatação de moeda', () => {
      expect(unmaskCurrency('R$ 1.234,56')).toBe(1234.56)
    })

    it('trata valores sem símbolo', () => {
      expect(unmaskCurrency('1.234,56')).toBe(1234.56)
    })

    it('retorna 0 para string vazia', () => {
      expect(unmaskCurrency('')).toBe(0)
    })

    it('interpreta dígitos digitados como centavos', () => {
      expect(unmaskCurrency('4')).toBe(0.04)
      expect(unmaskCurrency('40')).toBe(0.4)
      expect(unmaskCurrency('400')).toBe(4)
    })
  })
})
