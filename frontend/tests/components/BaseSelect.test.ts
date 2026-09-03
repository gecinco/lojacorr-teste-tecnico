import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseSelect from '../../components/BaseSelect.vue'

describe('BaseSelect', () => {
  const options = [
    { value: 'SP', label: 'SP - São Paulo' },
    { value: 'RJ', label: 'RJ - Rio de Janeiro' },
  ]

  it('emite o valor da opção clicada', async () => {
    const wrapper = mount(BaseSelect, {
      props: {
        modelValue: null,
        options,
        placeholder: 'Selecione...',
      },
      attachTo: document.body,
    })

    await wrapper.get('button').trigger('click')

    const items = document.querySelectorAll('[data-select-menu] button')
    expect(items.length).toBeGreaterThan(1)

    await (items[1] as HTMLButtonElement).click()

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['SP'])
    wrapper.unmount()
  })

  it('mostra o label da opção selecionada', () => {
    const wrapper = mount(BaseSelect, {
      props: {
        modelValue: 'RJ',
        options,
      },
    })

    expect(wrapper.get('button').text()).toContain('RJ - Rio de Janeiro')
  })
})
