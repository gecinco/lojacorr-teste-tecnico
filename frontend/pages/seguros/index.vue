<script setup lang="ts">
import type { SeguroFilters, SeguroStatus } from '~/types'
import { useMasks } from '~/composables/useMasks'

definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const seguroStore = useSeguroStore()
const dataStore = useDataStore()
const masks = useMasks()

const showFilters = ref(false)

const filters = reactive<SeguroFilters>({
  documento: '',
  status: null,
  inicio_vigencia_de: '',
  inicio_vigencia_ate: '',
  fim_vigencia_de: '',
  fim_vigencia_ate: '',
  seguradora_id: null,
  ramo_id: null,
})

const documentoFiltro = computed({
  get: () => masks.maskDocumento(filters.documento || ''),
  set: (value: string) => {
    filters.documento = masks.unmaskDocument(value)
  },
})

const onFilterDate = (field: 'inicio_vigencia_de' | 'fim_vigencia_ate', event: Event) => {
  const target = event.target as HTMLInputElement
  const value = masks.normalizeDate(target.value)
  target.value = value
  filters[field] = value
}

const sortConfig = reactive({
  column: 'created_at',
  order: 'desc' as 'asc' | 'desc',
})

onMounted(async () => {
  await Promise.all([
    seguroStore.fetchSeguros(),
    seguroStore.fetchSummary(),
    dataStore.fetchAllData(),
  ])
})

const handleFilter = async () => {
  seguroStore.setFilters({
    ...filters,
    page: 1,
  })
  showFilters.value = false
  await seguroStore.fetchSeguros()
}

const handleClearFilters = async () => {
  Object.assign(filters, {
    documento: '',
    status: null,
    inicio_vigencia_de: '',
    inicio_vigencia_ate: '',
    fim_vigencia_de: '',
    fim_vigencia_ate: '',
    seguradora_id: null,
    ramo_id: null,
  })
  seguroStore.resetFilters()
  await seguroStore.fetchSeguros()
}

const removeFilter = async (key: keyof SeguroFilters) => {
  const emptyValue = key === 'seguradora_id' || key === 'ramo_id' || key === 'status' ? null : ''
  ;(filters as Record<string, unknown>)[key] = emptyValue
  seguroStore.setFilters({ [key]: emptyValue, page: 1 })
  await seguroStore.fetchSeguros()
}

/** Cards de resumo filtram por status: clique aplica, clique de novo remove. */
const toggleStatusFilter = async (status: SeguroStatus | null) => {
  const novo: SeguroStatus | null = seguroStore.filters.status === status ? null : status
  filters.status = novo
  seguroStore.setFilters({ status: novo, page: 1 })
  await seguroStore.fetchSeguros()
}

const activeStatusFilter = computed<SeguroStatus | null>(() => seguroStore.filters.status ?? null)

const handleSort = async (column: string) => {
  if (sortConfig.column === column) {
    sortConfig.order = sortConfig.order === 'asc' ? 'desc' : 'asc'
  } else {
    sortConfig.column = column
    sortConfig.order = 'asc'
  }
  
  seguroStore.setSorting(sortConfig.column, sortConfig.order)
  await seguroStore.fetchSeguros()
}

const handlePageChange = async (page: number) => {
  seguroStore.setPage(page)
  await seguroStore.fetchSeguros()
}

const handlePerPageChange = async (event: Event) => {
  const target = event.target as HTMLSelectElement
  seguroStore.setFilters({ per_page: parseInt(target.value), page: 1 })
  await seguroStore.fetchSeguros()
}

const getSortIcon = (column: string) => {
  if (sortConfig.column !== column) return '↕'
  return sortConfig.order === 'asc' ? '↑' : '↓'
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'vigente':
      return 'badge-success'
    case 'a_vencer':
      return 'badge-warning'
    case 'vencido':
      return 'badge-danger'
    default:
      return 'badge'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'vigente':
      return 'Vigente'
    case 'a_vencer':
      return 'A Vencer'
    case 'vencido':
      return 'Vencido'
    default:
      return status
  }
}

const formatDate = (value: string) => {
  const [year, month, day] = value.split('-')
  if (!year || !month || !day) return value
  return `${day}/${month}/${year}`
}

const appliedFilters = computed(() => seguroStore.filters)

const activeFilterChips = computed(() => {
  const chips: { key: keyof SeguroFilters; label: string }[] = []
  const current = appliedFilters.value

  if (current.documento) {
    chips.push({ key: 'documento', label: `CPF/CNPJ: ${masks.maskDocumento(current.documento)}` })
  }

  if (current.seguradora_id) {
    const nome = dataStore.seguradoras.find(s => s.id === current.seguradora_id)?.nome
    chips.push({ key: 'seguradora_id', label: `Seguradora: ${nome || current.seguradora_id}` })
  }

  if (current.ramo_id) {
    const nome = dataStore.ramos.find(r => r.id === current.ramo_id)?.nome
    chips.push({ key: 'ramo_id', label: `Ramo: ${nome || current.ramo_id}` })
  }

  if (current.status) {
    chips.push({ key: 'status', label: `Status: ${getStatusLabel(current.status)}` })
  }

  if (current.inicio_vigencia_de) {
    chips.push({ key: 'inicio_vigencia_de', label: `Vigência de: ${formatDate(current.inicio_vigencia_de)}` })
  }

  if (current.fim_vigencia_ate) {
    chips.push({ key: 'fim_vigencia_ate', label: `Vigência até: ${formatDate(current.fim_vigencia_ate)}` })
  }

  return chips
})

const hasActiveFilters = computed(() => activeFilterChips.value.length > 0)
</script>

<template>
  <div class="space-y-6">
    <!-- Hero de marca -->
    <section class="relative overflow-hidden rounded-2xl brand-hero p-6 sm:p-8 text-white shadow-brand-lg">
      <div class="absolute inset-0 pointer-events-none opacity-20" aria-hidden="true">
        <svg class="absolute -right-10 -top-10 w-72 h-72 text-white" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="100" cy="100" r="30"/>
          <circle cx="100" cy="100" r="50"/>
          <circle cx="100" cy="100" r="70"/>
          <circle cx="100" cy="100" r="90"/>
        </svg>
      </div>
      <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
          <div class="brand-chip mb-3">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
            Painel do Corretor
          </div>
          <h1 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight">Meus Seguros</h1>
          <p class="mt-2 text-white/80 max-w-xl">
            Acompanhe, filtre e gerencie toda a sua carteira de apólices emissões, vigências e status de cobertura em um só lugar.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <button
            @click="showFilters = !showFilters"
            :class="[
              'inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all backdrop-blur',
              (showFilters || hasActiveFilters)
                ? 'bg-white text-primary-700 ring-2 ring-white/60 shadow-brand'
                : 'bg-white/10 text-white ring-1 ring-white/25 hover:bg-white/20'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filtros
            <span
              v-if="hasActiveFilters"
              class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-accent-600 text-white text-xs font-bold"
            >
              {{ activeFilterChips.length }}
            </span>
          </button>
          <NuxtLink
            to="/seguros/novo"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-primary-800 bg-white hover:bg-accent-50 shadow-brand-lg transition-all hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nova Contratação
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Stats de resumo (clicáveis: filtram a listagem por status) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <button
        type="button"
        class="card p-4 flex items-center gap-3 border-t-4 border-t-primary-600 text-left transition-all hover:shadow-brand-sm hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        :class="{ 'ring-2 ring-primary-500': !activeStatusFilter }"
        :aria-pressed="!activeStatusFilter"
        title="Mostrar todos os seguros"
        @click="toggleStatusFilter(null)"
      >
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary-600 to-wine-700 text-white flex items-center justify-center shadow-brand-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</p>
          <p class="text-lg font-bold text-primary-800">{{ seguroStore.summary.total }}</p>
        </div>
      </button>
      <button
        type="button"
        class="card p-4 flex items-center gap-3 border-t-4 border-t-success-500 text-left transition-all hover:shadow-brand-sm hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-success-500"
        :class="{ 'ring-2 ring-success-500': activeStatusFilter === 'vigente' }"
        :aria-pressed="activeStatusFilter === 'vigente'"
        title="Filtrar seguros vigentes"
        @click="toggleStatusFilter('vigente')"
      >
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-success-500 to-success-700 text-white flex items-center justify-center shadow-brand-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Vigentes</p>
          <p class="text-lg font-bold text-success-700">{{ seguroStore.summary.vigente }}</p>
        </div>
      </button>
      <button
        type="button"
        class="card p-4 flex items-center gap-3 border-t-4 border-t-warning-500 text-left transition-all hover:shadow-brand-sm hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning-500"
        :class="{ 'ring-2 ring-warning-500': activeStatusFilter === 'a_vencer' }"
        :aria-pressed="activeStatusFilter === 'a_vencer'"
        title="Filtrar seguros a vencer"
        @click="toggleStatusFilter('a_vencer')"
      >
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-warning-500 to-warning-700 text-white flex items-center justify-center shadow-brand-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M12 3a9 9 0 100 18 9 9 0 000-18z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">A vencer</p>
          <p class="text-lg font-bold text-warning-700">{{ seguroStore.summary.a_vencer }}</p>
        </div>
      </button>
      <button
        type="button"
        class="card p-4 flex items-center gap-3 border-t-4 border-t-danger-500 text-left transition-all hover:shadow-brand-sm hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger-500"
        :class="{ 'ring-2 ring-danger-500': activeStatusFilter === 'vencido' }"
        :aria-pressed="activeStatusFilter === 'vencido'"
        title="Filtrar seguros vencidos"
        @click="toggleStatusFilter('vencido')"
      >
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-danger-500 to-accent-700 text-white flex items-center justify-center shadow-brand-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Vencidos</p>
          <p class="text-lg font-bold text-danger-700">{{ seguroStore.summary.vencido }}</p>
        </div>
      </button>
    </div>

    <!-- Filters Panel -->
    <Transition name="slide">
      <div v-if="showFilters" class="card">
        <div class="px-4 py-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
              <label class="label text-xs mb-1">CPF/CNPJ</label>
              <input
                v-model="documentoFiltro"
                type="text"
                class="input py-1.5 text-sm font-mono"
                placeholder="000.000.000-00"
                maxlength="18"
              />
            </div>
            <div>
              <label class="label text-xs mb-1">Seguradora</label>
              <select v-model="filters.seguradora_id" class="input py-1.5 text-sm">
                <option :value="null">Todas</option>
                <option v-for="seg in dataStore.seguradoras" :key="seg.id" :value="seg.id">
                  {{ seg.nome }}
                </option>
              </select>
            </div>
            <div>
              <label class="label text-xs mb-1">Ramo</label>
              <select v-model="filters.ramo_id" class="input py-1.5 text-sm">
                <option :value="null">Todos</option>
                <option v-for="ramo in dataStore.ramos" :key="ramo.id" :value="ramo.id">
                  {{ ramo.nome }}
                </option>
              </select>
            </div>
            <div>
              <label class="label text-xs mb-1">Vigência de</label>
              <input
                :value="filters.inicio_vigencia_de"
                type="date"
                min="1900-01-01"
                max="2100-12-31"
                class="input py-1.5 text-sm"
                @input="onFilterDate('inicio_vigencia_de', $event)"
              />
            </div>
            <div>
              <label class="label text-xs mb-1">Vigência até</label>
              <input
                :value="filters.fim_vigencia_ate"
                type="date"
                min="1900-01-01"
                max="2100-12-31"
                class="input py-1.5 text-sm"
                @input="onFilterDate('fim_vigencia_ate', $event)"
              />
            </div>
          </div>
          <div class="flex justify-end gap-2 mt-3">
            <button @click="handleClearFilters" class="btn-secondary py-1.5 px-3 text-sm">
              Limpar
            </button>
            <button @click="handleFilter" class="btn-primary py-1.5 px-3 text-sm">
              Aplicar
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Active filters -->
    <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2">
      <span class="text-sm font-medium text-gray-600">Filtros ativos:</span>
      <button
        v-for="chip in activeFilterChips"
        :key="chip.key"
        type="button"
        class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 text-primary-700 border border-primary-200 px-3 py-1 text-xs font-medium hover:bg-primary-100 transition-colors"
        @click="removeFilter(chip.key)"
      >
        {{ chip.label }}
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <button
        type="button"
        class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700 hover:text-primary-900 hover:underline underline-offset-2"
        @click="showFilters = true"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Adicionar filtros
      </button>
      <button
        type="button"
        class="text-xs font-medium text-gray-500 hover:text-danger-600 underline-offset-2 hover:underline"
        @click="handleClearFilters"
      >
        Limpar todos
      </button>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body p-0">
        <!-- Loading skeleton -->
        <div v-if="seguroStore.loading" class="p-4 space-y-3" aria-busy="true" aria-label="Carregando seguros">
          <div class="h-4 bg-gray-100 rounded w-full animate-pulse"></div>
          <div v-for="n in 5" :key="n" class="flex gap-4">
            <div class="h-4 bg-gray-100 rounded flex-1 animate-pulse"></div>
            <div class="h-4 bg-gray-100 rounded flex-1 animate-pulse"></div>
            <div class="h-4 bg-gray-100 rounded w-24 animate-pulse"></div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="seguroStore.error" class="text-center py-12 px-4">
          <svg class="mx-auto h-12 w-12 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Falha ao carregar</h3>
          <p class="mt-1 text-sm text-gray-500">{{ seguroStore.error }}</p>
          <div class="mt-6">
            <button type="button" class="btn-primary" @click="seguroStore.fetchSeguros()">
              Tentar novamente
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="seguroStore.seguros.length === 0" class="text-center py-12 px-4">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <template v-if="hasActiveFilters">
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum seguro com esses filtros</h3>
            <p class="mt-1 text-sm text-gray-500">Ajuste os filtros ou limpe-os para ver toda a carteira.</p>
            <div class="mt-6">
              <button type="button" class="btn-secondary" @click="handleClearFilters">
                Limpar filtros
              </button>
            </div>
          </template>
          <template v-else>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum seguro encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Comece contratando um novo seguro.</p>
            <div class="mt-6">
              <NuxtLink to="/seguros/novo" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Seguro
              </NuxtLink>
            </div>
          </template>
        </div>

        <!-- Table Content -->
        <div v-else class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th class="sortable" @click="handleSort('documento_segurado')">
                  CPF/CNPJ {{ getSortIcon('documento_segurado') }}
                </th>
                <th class="sortable" @click="handleSort('nome_segurado')">
                  Segurado {{ getSortIcon('nome_segurado') }}
                </th>
                <th>Seguradora</th>
                <th>Ramo</th>
                <th class="sortable" @click="handleSort('valor_total')">
                  Valor {{ getSortIcon('valor_total') }}
                </th>
                <th class="sortable" @click="handleSort('inicio_vigencia')">
                  Início {{ getSortIcon('inicio_vigencia') }}
                </th>
                <th class="sortable" @click="handleSort('fim_vigencia')">
                  Fim {{ getSortIcon('fim_vigencia') }}
                </th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="seguro in seguroStore.seguros" :key="seguro.id">
                <td class="font-mono text-sm text-primary-800 font-semibold">{{ seguro.documento_formatado }}</td>
                <td>
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-wine-700 text-white flex items-center justify-center text-xs font-bold shadow-brand-sm">
                      {{ seguro.nome_segurado?.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium text-gray-800">{{ seguro.nome_segurado }}</span>
                  </div>
                </td>
                <td>
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                    {{ seguro.seguradora?.nome }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-primary-50 text-primary-700 text-xs font-semibold ring-1 ring-primary-100">
                    {{ seguro.ramo?.nome }}
                  </span>
                </td>
                <td class="font-bold text-primary-800">{{ seguro.valor_total_formatado }}</td>
                <td class="text-gray-600">{{ seguro.inicio_vigencia_formatado }}</td>
                <td class="text-gray-600">{{ seguro.fim_vigencia_formatado }}</td>
                <td>
                  <span :class="[getStatusBadgeClass(seguro.status_vigencia), 'gap-1.5']">
                    <span
                      :class="[
                        'w-1.5 h-1.5 rounded-full',
                        seguro.status_vigencia === 'vigente' && 'bg-success-500 animate-pulse',
                        seguro.status_vigencia === 'a_vencer' && 'bg-warning-500 animate-pulse',
                        seguro.status_vigencia === 'vencido' && 'bg-danger-500'
                      ]"
                    ></span>
                    {{ getStatusLabel(seguro.status_vigencia) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="seguroStore.seguros.length > 0" class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-700">Exibir</span>
            <select
              :value="seguroStore.pagination.perPage"
              @change="handlePerPageChange"
              class="input w-20 py-1 text-sm"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-700">por página</span>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-700">
              {{ seguroStore.pagination.total }} registro(s)
            </span>
          </div>

          <div class="flex items-center gap-1">
            <button
              @click="handlePageChange(seguroStore.pagination.currentPage - 1)"
              :disabled="seguroStore.pagination.currentPage === 1"
              class="btn-secondary py-1 px-3 text-sm"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm text-gray-700">
              {{ seguroStore.pagination.currentPage }} / {{ seguroStore.pagination.lastPage }}
            </span>
            <button
              @click="handlePageChange(seguroStore.pagination.currentPage + 1)"
              :disabled="seguroStore.pagination.currentPage === seguroStore.pagination.lastPage"
              class="btn-secondary py-1 px-3 text-sm"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
