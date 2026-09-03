import type { SeguroFormData } from '~/types'
import { ApiError } from '~/composables/useApi'

const FIELD_ORDER: Array<keyof SeguroFormData> = [
  'documento_segurado',
  'nome_segurado',
  'seguradora_id',
  'ramo_id',
  'valor_total',
  'valor_parcela',
  'inicio_vigencia',
  'fim_vigencia',
  'cep',
  'logradouro',
  'bairro',
  'cidade',
  'uf',
]

export function useSeguroForm() {
  const seguroStore = useSeguroStore()
  const toast = useToast()
  const router = useRouter()
  const validation = useValidation()
  const masks = useMasks()

  const form = reactive<SeguroFormData>({
    documento_segurado: '',
    nome_segurado: '',
    seguradora_id: null,
    ramo_id: null,
    valor_total: null,
    quantidade_parcelas: 1,
    valor_parcela: null,
    inicio_vigencia: '',
    fim_vigencia: '',
    cep: '',
    logradouro: '',
    numero: '',
    complemento: '',
    bairro: '',
    cidade: '',
    uf: '',
  })

  const errors = reactive<Record<string, string>>({})
  const loading = ref(false)

  const tipoDocumento = computed(() => {
    const doc = form.documento_segurado.replace(/[\s./-]/g, '').toUpperCase()
    return /^\d{1,11}$/.test(doc) ? 'cpf' : 'cnpj'
  })

  const documentoMasked = computed({
    get: () => masks.maskDocumento(form.documento_segurado),
    set: (value: string) => {
      form.documento_segurado = value
    },
  })

  const cepMasked = computed({
    get: () => masks.maskCep(form.cep),
    set: (value: string) => {
      form.cep = value
    },
  })

  const calcularParcela = () => {
    if (form.valor_total && form.quantidade_parcelas > 0) {
      form.valor_parcela = Math.round((form.valor_total / form.quantidade_parcelas) * 100) / 100
    }
  }

  const resumoFinanceiro = computed(() => {
    if (!form.valor_total || !form.valor_parcela || form.quantidade_parcelas <= 0) {
      return null
    }

    const produto = Math.round(form.valor_parcela * form.quantidade_parcelas * 100) / 100
    const coerencia = validation.validateCoerenciaFinanceira(
      form.valor_total,
      form.quantidade_parcelas,
      form.valor_parcela,
    )

    return {
      parcelas: form.quantidade_parcelas,
      parcelaFormatada: masks.maskCurrency(form.valor_parcela),
      totalFormatado: masks.maskCurrency(form.valor_total),
      produtoFormatado: masks.maskCurrency(produto),
      coerente: coerencia.valid,
      mensagem: coerencia.message,
    }
  })

  const clearError = (field: string) => {
    delete errors[field]
  }

  const validateField = (field: keyof SeguroFormData | string): boolean => {
    switch (field) {
      case 'documento_segurado': {
        const result = validation.validateDocumento(form.documento_segurado)
        if (!result.valid) {
          errors.documento_segurado = result.message
          return false
        }
        clearError('documento_segurado')
        return true
      }
      case 'nome_segurado': {
        if (!form.nome_segurado || form.nome_segurado.trim().length < 3) {
          errors.nome_segurado = 'Nome deve ter no mínimo 3 caracteres'
          return false
        }
        clearError('nome_segurado')
        return true
      }
      case 'seguradora_id': {
        if (!form.seguradora_id) {
          errors.seguradora_id = 'Selecione uma seguradora'
          return false
        }
        clearError('seguradora_id')
        return true
      }
      case 'ramo_id': {
        if (!form.ramo_id) {
          errors.ramo_id = 'Selecione um ramo'
          return false
        }
        clearError('ramo_id')
        return true
      }
      case 'valor_total': {
        if (!form.valor_total || form.valor_total <= 0) {
          errors.valor_total = 'Valor total é obrigatório'
          return false
        }
        clearError('valor_total')
        return true
      }
      case 'valor_parcela': {
        if (!form.valor_parcela || form.valor_parcela <= 0) {
          errors.valor_parcela = 'Valor da parcela é obrigatório'
          return false
        }
        if (form.valor_total && form.quantidade_parcelas) {
          const coerencia = validation.validateCoerenciaFinanceira(
            form.valor_total,
            form.quantidade_parcelas,
            form.valor_parcela,
          )
          if (!coerencia.valid) {
            errors.valor_parcela = coerencia.message
            return false
          }
        }
        clearError('valor_parcela')
        return true
      }
      case 'inicio_vigencia': {
        if (!form.inicio_vigencia) {
          errors.inicio_vigencia = 'Data de início é obrigatória'
          return false
        }
        clearError('inicio_vigencia')
        if (form.fim_vigencia) {
          validateField('fim_vigencia')
        }
        return true
      }
      case 'fim_vigencia': {
        const vigencia = validation.validateVigencia(form.inicio_vigencia, form.fim_vigencia)
        if (!vigencia.valid) {
          errors.fim_vigencia = vigencia.message
          return false
        }
        clearError('fim_vigencia')
        return true
      }
      case 'cep': {
        if (!form.cep || form.cep.replace(/\D/g, '').length !== 8) {
          errors.cep = 'CEP inválido'
          return false
        }
        clearError('cep')
        return true
      }
      case 'logradouro': {
        if (!form.logradouro) {
          errors.logradouro = 'Logradouro é obrigatório'
          return false
        }
        clearError('logradouro')
        return true
      }
      case 'bairro': {
        if (!form.bairro) {
          errors.bairro = 'Bairro é obrigatório'
          return false
        }
        clearError('bairro')
        return true
      }
      case 'cidade': {
        if (!form.cidade) {
          errors.cidade = 'Cidade é obrigatória'
          return false
        }
        clearError('cidade')
        return true
      }
      case 'uf': {
        if (!form.uf || form.uf.length !== 2) {
          errors.uf = 'UF inválida'
          return false
        }
        clearError('uf')
        return true
      }
      default:
        return true
    }
  }

  const validateForm = (): boolean => {
    Object.keys(errors).forEach(key => delete errors[key])
    let isValid = true

    FIELD_ORDER.forEach((field) => {
      if (!validateField(field)) {
        isValid = false
      }
    })

    return isValid
  }

  const scrollToFirstError = async () => {
    await nextTick()
    document.querySelector('[data-field-error]')?.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
    })
  }

  const handleSubmit = async () => {
    if (!validateForm()) {
      toast.error('Corrija os erros do formulário')
      await scrollToFirstError()
      return
    }

    loading.value = true

    try {
      await seguroStore.createSeguro(form)
      toast.success('Seguro contratado com sucesso!')
      router.push('/seguros')
    } catch (error) {
      if (error instanceof ApiError && error.errors) {
        Object.entries(error.errors).forEach(([key, messages]) => {
          errors[key] = messages[0]
        })
      }
      toast.error('Erro ao contratar seguro')
      await scrollToFirstError()
    } finally {
      loading.value = false
    }
  }

  watch(() => form.quantidade_parcelas, () => {
    calcularParcela()
    if (form.valor_parcela) {
      validateField('valor_parcela')
    }
  })

  watch(() => form.valor_total, () => {
    calcularParcela()
  })

  // Limpa automaticamente erros dos campos de endereço quando preenchidos
  // (ex: após busca de CEP preencher logradouro/bairro/cidade/uf)
  const addressFields: Array<keyof SeguroFormData> = ['logradouro', 'bairro', 'cidade', 'uf']
  addressFields.forEach((field) => {
    watch(() => form[field], (value) => {
      if (errors[field] && value && String(value).trim().length > 0) {
        clearError(field)
      }
    })
  })

  return {
    form,
    errors,
    loading,
    tipoDocumento,
    documentoMasked,
    cepMasked,
    resumoFinanceiro,
    calcularParcela,
    validateField,
    handleSubmit,
  }
}
