import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MoneyInput from '../../components/MoneyInput.vue'

describe('MoneyInput', () => {
  it('emite centavos ao digitar o primeiro dígito', async () => {
    const wrapper = mount(MoneyInput, {
      props: {
        modelValue: null,
        label: 'Valor Total',
      },
    })

    await wrapper.get('input').setValue('4')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([0.04])
  })

  it('formata o valor recebido em reais', () => {
    const wrapper = mount(MoneyInput, {
      props: {
        modelValue: 4,
        label: 'Valor Total',
      },
    })

    const formatted = (wrapper.get('input').element as HTMLInputElement).value.replace(/\u00a0/g, ' ')
    expect(formatted).toBe('R$ 4,00')
  })

  it('exibe mensagem de erro e aria-invalid', () => {
    const wrapper = mount(MoneyInput, {
      props: {
        modelValue: null,
        id: 'valor-total',
        label: 'Valor Total',
        error: 'Valor total é obrigatório',
      },
    })

    expect(wrapper.get('input').attributes('aria-invalid')).toBe('true')
    expect(wrapper.text()).toContain('Valor total é obrigatório')
  })
})
