import type { SeguroFormData } from '~/types'

export function useCep(form: SeguroFormData) {
  const dataStore = useDataStore()
  const toast = useToast()

  const loadingCep = ref(false)
  const cepNotFound = ref(false)

  const buscarCep = async () => {
    const cepLimpo = form.cep.replace(/\D/g, '')

    if (cepLimpo.length !== 8) return

    loadingCep.value = true
    cepNotFound.value = false

    try {
      const endereco = await dataStore.fetchEnderecoByCep(cepLimpo)

      if (endereco) {
        form.logradouro = endereco.logradouro
        form.bairro = endereco.bairro
        form.cidade = endereco.cidade
        form.uf = endereco.uf
        form.complemento = endereco.complemento || ''
      } else {
        cepNotFound.value = true
        toast.warning('CEP não encontrado. Preencha o endereço manualmente.')
      }
    } catch {
      cepNotFound.value = true
      toast.warning('Erro ao buscar CEP. Preencha o endereço manualmente.')
    } finally {
      loadingCep.value = false
    }
  }

  return {
    loadingCep,
    cepNotFound,
    buscarCep,
  }
}
