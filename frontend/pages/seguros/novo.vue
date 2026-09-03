<script setup lang="ts">
import { UF_SELECT_OPTIONS } from '~/constants/ufs'

definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const dataStore = useDataStore()
const {
  form,
  errors,
  loading,
  cepMasked,
  resumoFinanceiro,
  validateField,
  handleSubmit,
} = useSeguroForm()
const { loadingCep, cepNotFound, buscarCep } = useCep(form)

const seguradoraOptions = computed(() =>
  dataStore.seguradoras.map(seg => ({ value: seg.id, label: seg.nome })),
)

const ramoOptions = computed(() =>
  dataStore.ramos.map(ramo => ({ value: ramo.id, label: ramo.nome })),
)

const parcelaOptions = computed(() =>
  Array.from({ length: 12 }, (_, index) => ({
    value: index + 1,
    label: `${index + 1}x`,
  })),
)

onMounted(async () => {
  await dataStore.fetchAllData()
})

const onCepBlur = async () => {
  validateField('cep')
  await buscarCep()
}
</script>

<template>
  <div class="max-w-4xl mx-auto pb-24">
    <!-- Breadcrumb / Voltar -->
    <NuxtLink to="/seguros" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-900 transition-colors mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
      Voltar para lista
    </NuxtLink>

    <!-- Hero de marca -->
    <section class="relative overflow-hidden rounded-2xl brand-hero p-6 sm:p-8 text-white shadow-brand-lg mb-8">
      <div class="absolute inset-0 pointer-events-none opacity-20" aria-hidden="true">
        <svg class="absolute -right-8 -bottom-8 w-64 h-64 text-white" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="100" cy="100" r="30"/>
          <circle cx="100" cy="100" r="50"/>
          <circle cx="100" cy="100" r="70"/>
          <circle cx="100" cy="100" r="90"/>
        </svg>
      </div>
      <div class="relative">
        <div class="brand-chip mb-3">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Nova Contratação
        </div>
        <h1 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight">Contratar Seguro</h1>
        <p class="mt-2 text-white/80 max-w-2xl">
          Preencha os dados abaixo para emitir uma nova apólice. Todos os campos são validados em tempo real
          e o CEP é preenchido automaticamente.
        </p>
      </div>
    </section>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="card">
        <div class="card-header flex items-center gap-3">
          <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center font-extrabold text-base shadow-brand-sm">1</span>
          <div>
            <h2 class="text-lg font-bold text-primary-800">Dados do Segurado</h2>
            <p class="text-xs text-gray-500">Identificação da pessoa ou empresa que será segurada</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <DocumentInput
              id="documento"
              v-model="form.documento_segurado"
              label="CPF/CNPJ"
              :error="errors.documento_segurado"
              @blur="validateField('documento_segurado')"
            />
            <BaseInput
              id="nome"
              v-model="form.nome_segurado"
              label="Nome do Segurado"
              placeholder="Nome completo"
              :error="errors.nome_segurado"
              @blur="validateField('nome_segurado')"
            />
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header flex items-center gap-3">
          <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center font-extrabold text-base shadow-brand-sm">2</span>
          <div>
            <h2 class="text-lg font-bold text-primary-800">Dados do Seguro</h2>
            <p class="text-xs text-gray-500">Seguradora e ramo de atuação</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <BaseSelect
              id="seguradora"
              v-model="form.seguradora_id"
              label="Seguradora"
              :options="seguradoraOptions"
              :error="errors.seguradora_id"
              @blur="validateField('seguradora_id')"
            />
            <BaseSelect
              id="ramo"
              v-model="form.ramo_id"
              label="Ramo"
              :options="ramoOptions"
              :error="errors.ramo_id"
              @blur="validateField('ramo_id')"
            />
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header flex items-center gap-3">
          <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center font-extrabold text-base shadow-brand-sm">3</span>
          <div>
            <h2 class="text-lg font-bold text-primary-800">Valores</h2>
            <p class="text-xs text-gray-500">Prêmio total e condição de parcelamento</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <MoneyInput
              id="valor-total"
              v-model="form.valor_total"
              label="Valor Total"
              :error="errors.valor_total"
              @blur="validateField('valor_total')"
            />
            <BaseSelect
              id="parcelas"
              v-model="form.quantidade_parcelas"
              label="Parcelas"
              :options="parcelaOptions"
              :clearable="false"
            />
            <MoneyInput
              id="valor-parcela"
              v-model="form.valor_parcela"
              label="Valor da Parcela"
              :error="errors.valor_parcela"
              @blur="validateField('valor_parcela')"
            />
          </div>
          <div
            v-if="resumoFinanceiro"
            class="mt-4 flex items-start gap-3 rounded-xl px-4 py-3 ring-1"
            :class="resumoFinanceiro.coerente
              ? 'bg-success-50 text-success-700 ring-success-500/20'
              : 'bg-danger-50 text-danger-700 ring-danger-500/20'"
          >
            <svg v-if="resumoFinanceiro.coerente" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <svg v-else class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="text-sm font-medium">
              <template v-if="resumoFinanceiro.coerente">
                {{ resumoFinanceiro.parcelas }}x de {{ resumoFinanceiro.parcelaFormatada }}
                = {{ resumoFinanceiro.produtoFormatado }}
              </template>
              <template v-else>
                {{ resumoFinanceiro.mensagem }}
              </template>
            </p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header flex items-center gap-3">
          <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center font-extrabold text-base shadow-brand-sm">4</span>
          <div>
            <h2 class="text-lg font-bold text-primary-800">Vigência</h2>
            <p class="text-xs text-gray-500">Período de cobertura da apólice</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <BaseInput
              id="inicio-vigencia"
              v-model="form.inicio_vigencia"
              type="date"
              label="Início da Vigência"
              :error="errors.inicio_vigencia"
              @blur="validateField('inicio_vigencia')"
            />
            <BaseInput
              id="fim-vigencia"
              v-model="form.fim_vigencia"
              type="date"
              label="Fim da Vigência"
              :error="errors.fim_vigencia"
              @blur="validateField('fim_vigencia')"
            />
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header flex items-center gap-3">
          <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center font-extrabold text-base shadow-brand-sm">5</span>
          <div>
            <h2 class="text-lg font-bold text-primary-800">Endereço</h2>
            <p class="text-xs text-gray-500">Digite o CEP e completaremos o endereço automaticamente</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div :data-field-error="errors.cep ? true : undefined">
              <label class="label" for="cep">CEP</label>
              <div class="relative">
                <input
                  id="cep"
                  v-model="cepMasked"
                  type="text"
                  class="input pr-10"
                  :class="{ 'input-error': errors.cep }"
                  placeholder="00000-000"
                  maxlength="9"
                  :aria-invalid="errors.cep ? true : undefined"
                  @blur="onCepBlur"
                />
                <div v-if="loadingCep" class="absolute right-3 top-1/2 -translate-y-1/2">
                  <div class="animate-spin h-5 w-5 border-2 border-primary-600 border-t-transparent rounded-full"></div>
                </div>
              </div>
              <p v-if="errors.cep" class="error-text">{{ errors.cep }}</p>
              <p v-if="cepNotFound" class="mt-1 text-sm text-warning-600">
                CEP não encontrado. Preencha manualmente.
              </p>
            </div>
            <div class="md:col-span-2">
              <BaseInput
                id="logradouro"
                v-model="form.logradouro"
                label="Logradouro"
                placeholder="Rua, Avenida, etc."
                :error="errors.logradouro"
                @blur="validateField('logradouro')"
              />
            </div>
            <BaseInput
              id="numero"
              v-model="form.numero"
              label="Número"
              placeholder="123"
            />
            <BaseInput
              id="complemento"
              v-model="form.complemento"
              label="Complemento"
              placeholder="Apto, Sala, etc."
            />
            <BaseInput
              id="bairro"
              v-model="form.bairro"
              label="Bairro"
              placeholder="Bairro"
              :error="errors.bairro"
              @blur="validateField('bairro')"
            />
            <BaseInput
              id="cidade"
              v-model="form.cidade"
              label="Cidade"
              placeholder="Cidade"
              :error="errors.cidade"
              @blur="validateField('cidade')"
            />
            <BaseSelect
              id="uf"
              :model-value="form.uf || null"
              label="UF"
              :options="UF_SELECT_OPTIONS"
              :error="errors.uf"
              @update:model-value="form.uf = $event ? String($event) : ''"
              @blur="validateField('uf')"
            />
          </div>
        </div>
      </div>

      <!-- Barra de ações fixa inferior -->
      <div class="sticky bottom-4 z-10">
        <div class="rounded-2xl bg-white/95 backdrop-blur border border-primary-100 shadow-brand-lg p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-sm text-gray-600 hidden sm:block">
            <span class="font-semibold text-primary-800">Confira os dados</span> antes de emitir a apólice.
          </p>
          <div class="flex gap-3 w-full sm:w-auto">
            <NuxtLink to="/seguros" class="btn-secondary flex-1 sm:flex-none">
              Cancelar
            </NuxtLink>
            <button
              type="submit"
              class="btn-primary flex-1 sm:flex-none"
              :disabled="loading"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Contratando...' : 'Contratar Seguro' }}
              <svg v-if="!loading" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
