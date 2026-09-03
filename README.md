# Lojacorr Seguros

Sistema fullstack para contratação e gestão de seguros, desenvolvido como desafio técnico para a posição de Analista Desenvolvedor Fullstack Pleno na Lojacorr Seguros.

![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js)
![Nuxt](https://img.shields.io/badge/Nuxt-3.x-00DC82?style=flat-square&logo=nuxt.js)
![Laravel](https://img.shields.io/badge/Laravel-5.8-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-7.3+-777BB4?style=flat-square&logo=php)
![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=flat-square&logo=typescript)
![MariaDB](https://img.shields.io/badge/MariaDB-10.6-003545?style=flat-square&logo=mariadb)
![Redis](https://img.shields.io/badge/Redis-7.x-DC382D?style=flat-square&logo=redis)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker)

---

## Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Arquitetura e Decisões Técnicas](#arquitetura-e-decisões-técnicas)
- [Stack Tecnológica](#stack-tecnológica)
- [Pré-requisitos](#pré-requisitos)
- [Instalação e Execução](#instalação-e-execução)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [API Endpoints](#api-endpoints)
- [Testes](#testes)
- [Troubleshooting](#troubleshooting)
- [Uso de IA no Desenvolvimento](#uso-de-ia-no-desenvolvimento)
- [Melhorias Futuras](#melhorias-futuras)

---

## Sobre o Projeto

Este projeto simula um cenário real do dia a dia da Lojacorr: **contratar um seguro e visualizar a carteira do segurado**. O foco está em demonstrar boas práticas de desenvolvimento, código limpo e uma arquitetura escalável.

### Credenciais de Acesso (Seed)

| E-mail | Senha |
|--------|-------|
| admin@lojacorr.com.br | lojacorr2024 |
| corretor@lojacorr.com.br | corretor123 |

---

## Funcionalidades

### Autenticação
- Login com e-mail e senha
- Proteção de rotas (frontend e backend)
- Token JWT com refresh automático
- Usuários pré-cadastrados via seed

### Formulário de Contratação de Seguro
- CPF/CNPJ com máscara dinâmica (detecta automaticamente)
- Validação completa de CPF (dígitos verificadores)
- Validação completa de CNPJ (incluindo alfanumérico da regra 2026)
- Seleção de Seguradora e Ramo (via API)
- Valor total com máscara monetária pt-BR
- Parcelas de 1x a 12x
- Cálculo automático do valor da parcela
- Validação de coerência financeira (tolerância R$ 0,01)
- Datepickers para vigência com validação
- Busca de CEP via ViaCEP com fallback manual
- Timeout de 5 segundos para API de CEP

### Listagem de Seguros
- Tabela com todas as informações
- Filtros server-side (CPF/CNPJ, período, ramo, seguradora)
- Filtro por status clicando nos cards de resumo (Vigentes / A vencer / Vencidos): clique aplica, clique novamente remove
- Ordenação server-side em todas as colunas
- Paginação server-side (10/25/50 por página)
- Indicadores visuais de status (vigente, vencido, a vencer)

### Diferenciais Implementados
- Testes Frontend (Vitest + Vue Test Utils)
- Testes Backend (Pest PHP)
- Docker Compose completo
- Cache com Redis
- Logs de auditoria com MongoDB

---

## Arquitetura e Decisões Técnicas

### Backend (Laravel 5.8 + PHP 7.3)

**Por que Laravel 5.8 e PHP 7.3?**
A escolha dessas versões foi estratégica, baseada na informação de que a Lojacorr utiliza essa stack em produção. Demonstrar domínio em versões específicas mostra capacidade de adaptação e entendimento de que nem sempre trabalhamos com as tecnologias mais recentes.

**Arquitetura em Camadas:**
```
Controllers → Services → Repositories → Models
```

- **Controllers**: Responsáveis apenas por receber requisições e retornar respostas
- **Services**: Contêm a lógica de negócio
- **Repositories**: Abstraem o acesso ao banco de dados (padrão Repository)
- **Models**: Representam as entidades do domínio

**Decisões Técnicas:**
- **JWT Auth**: Escolhido pela simplicidade e stateless, ideal para APIs REST
- **Form Requests**: Validações isoladas em classes dedicadas, promovendo SRP
- **Resources/Transformers**: Padronização das respostas da API
- **Custom Rules**: Validações complexas (CPF/CNPJ, coerência financeira) em classes reutilizáveis
- **Autorização no repositório**: `update`/`delete` escopam o próprio SQL por `user_id`: o banco nunca cruza registros entre usuários, mesmo que uma camada superior falhe (proteção contra IDOR)

**Otimizações de Performance:**
- Queries da listagem com `select()` explícito de colunas necessárias e eager load seletivo (`seguradora:id,nome,codigo`)
- Endpoint de resumo agregado em uma única query (`CASE`/`SUM` no SQL) em vez de 3 filtros no frontend, que além de mais lentos, só refletiam a página atual (bug corrigido)
- Resumo cacheado por usuário (TTL curto + invalidação em mutações)
- Auditoria gravada por job em fila, após o commit da transação, fora do caminho crítico do CRUD
- Cache do middleware JWT: o `User` é hidratado uma única vez por request e reutilizado por controllers/services
- Frontend: `AbortController` nas listagens (evita race condition ao trocar filtros/página rápido), funções puras exportadas diretamente nos composables de máscara/validação, e listener de scroll `passive` com throttle por `requestAnimationFrame` no BaseSelect

### Frontend (Nuxt 3 + Vue 3)

**Por que Nuxt 3?**
- SSR out-of-the-box para melhor SEO (se necessário no futuro)
- Auto-imports de componentes e composables
- Roteamento baseado em arquivos
- Excelente DX com TypeScript

**Padrões Utilizados:**
- **Composition API**: Código mais organizado e reutilizável
- **Composables**: Lógica extraída em funções reutilizáveis
- **Pinia**: Store management moderno e type-safe
- **Tailwind CSS**: Estilização utility-first para produtividade

### Banco de Dados (MariaDB)

**Por que MariaDB?**
- Compatibilidade total com MySQL
- Performance superior em algumas operações
- Fork open-source do MySQL, mantido pela comunidade
- Requisito do desafio técnico

**Índices Criados:**
- `documento_segurado`: Busca por CPF/CNPJ
- `inicio_vigencia`, `fim_vigencia`: Filtros por período
- `(inicio_vigencia, fim_vigencia)`: Consultas de intervalo
- `(user_id, created_at)`: Ordenação padrão da listagem
- `(user_id, seguradora_id)`, `(user_id, ramo_id)`, `(user_id, documento_segurado)`, `(user_id, inicio_vigencia)`, `(user_id, fim_vigencia)`: Filtros compostos: todas as queries da API filtram por usuário, então `user_id` é o prefixo dos índices

### Cache (Redis)

**Uso do Redis:**
- **Cache de sessão**: Sessões JWT com TTL
- **Cache de dados estáticos**: Seguradoras e Ramos (1 hora de TTL, com invalidação automática via model events)
- **Cache do resumo por usuário**: Contagens de vigentes/a vencer/vencidos (TTL de 60s + invalidação ao mutar seguros)
- **Cache de CEP**: Respostas do ViaCEP (24 horas)
- **Rate limiting**: Proteção contra abuse da API (10/min nas rotas públicas, 120/min nas autenticadas)

### Logs de Auditoria (MongoDB)

**Como funciona:**
- Toda operação de CRUD em seguros gera um registro de auditoria (ação, estado anterior, estado novo, IP, user agent)
- A gravação é feita por um **job em fila** (`LogAuditRecord`), executado **após o commit** da transação SQL: a latência do MongoDB nunca fica no caminho crítico da resposta e nenhum log registra operação que sofreu rollback
- Falhas do job são registradas com retry (3 tentativas)

**Por que MongoDB para logs?**
- Schema flexível para diferentes tipos de eventos
- Excelente para writes pesados
- Separação de concerns (dados transacionais vs. logs)
- Facilidade para análises futuras

---

## Stack Tecnológica

| Camada | Tecnologia | Versão |
|--------|------------|--------|
| Frontend | Vue.js | 3.4+ |
| Frontend | Nuxt | 3.11+ |
| Frontend | TypeScript | 5.3+ |
| Frontend | Tailwind CSS | 3.4+ |
| Frontend | Pinia | 2.1+ |
| Backend | PHP | 7.3+ |
| Backend | Laravel | 5.8 |
| Backend | JWT Auth | 1.0 |
| Banco de Dados | MariaDB | 10.6 |
| Cache | Redis | 7.x |
| Logs | MongoDB | 6.x |
| Testes Frontend | Vitest | 1.6+ |
| Testes Backend | PHPUnit | 8.x |
| Infraestrutura | Docker | 24+ |
| Infraestrutura | Docker Compose | 2.x |

---

## Pré-requisitos

### Com Docker (Recomendado)
- Docker Desktop 24+ (Windows/Mac) ou Docker Engine + Compose 2.x (Linux)
- Git

### Sem Docker
- PHP 7.3+ com extensões: pdo_mysql, mbstring, bcmath, gd, zip
- Composer 2.x
- Node.js 18+
- npm 9+
- MariaDB 10.6+
- Redis 7+
- MongoDB 6+ (opcional, para logs de auditoria)

---

## Instalação e Execução

### Opção 1: Docker (Recomendado)

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/lojacorr-seguros.git
cd lojacorr-seguros

# 2. Copie o arquivo de ambiente do backend
# (necessário antes de subir os containers porque o volume monta essa pasta)
cp backend/.env.example backend/.env

# 3. Suba os containers (build + pull das imagens)
docker-compose up -d --build

# 4. Aguarde os containers ficarem saudáveis (~30 segundos)
docker-compose ps

# 5. Gere a chave da aplicação
docker-compose exec backend php artisan key:generate

# 6. Gere o segredo JWT
docker-compose exec backend php artisan jwt:secret

# 7. Execute as migrations e seeds
docker-compose exec backend php artisan migrate --seed

# 8. Acesse a aplicação
# Frontend:     http://localhost:3000
# Backend API:  http://localhost:8000
# PHPMyAdmin:   http://localhost:8080
```

> **Nota:** Se o pull de alguma imagem falhar por erro de rede/proxy, basta rodar `docker-compose up -d --build` novamente, o Docker retoma o download de onde parou.

### Opção 2: Instalação Manual

**Backend (terminal 1):**
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

**Frontend (terminal 2):**
```bash
cd frontend
npm install
npm run dev
```

> **Nota:** O frontend roda em `http://localhost:3000` e se comunica com `http://localhost:8000/api/v1` por padrão. Certifique-se de que o backend esteja rodando antes de iniciar o frontend.

---

## Estrutura do Projeto

```
lojacorr-seguros/
├── docker-compose.yml
├── backend/                     # API Laravel
│   ├── app/
│   │   ├── Console/
│   │   ├── Exceptions/
│   │   │   ├── BusinessException.php
│   │   │   └── Handler.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/V1/      # Controllers versionados
│   │   │   ├── Middleware/
│   │   │   ├── Requests/        # Form Requests
│   │   │   └── Resources/       # API Resources
│   │   ├── Models/
│   │   ├── Providers/
│   │   ├── Repositories/
│   │   │   ├── Contracts/       # Interfaces
│   │   │   └── Eloquent/        # Implementações
│   │   ├── Rules/               # Custom Validation Rules
│   │   ├── Services/            # Business Logic
│   │   ├── Jobs/                # Jobs em fila (LogAuditRecord)
│   │   └── Logging/             # Logger MongoDB
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeds/
│   ├── routes/
│   │   └── api.php
│   └── tests/
│       ├── Feature/
│       └── Unit/
│
└── frontend/                    # Nuxt 3 Application
    ├── assets/
    │   └── css/
    ├── components/
    ├── composables/             # Lógica reutilizável
    │   ├── useApi.ts            # Cliente HTTP com suporte a AbortSignal
    │   ├── useCep.ts
    │   ├── useMasks.ts
    │   ├── useSeguroForm.ts
    │   ├── useToast.ts
    │   └── useValidation.ts
    ├── layouts/
    ├── middleware/
    ├── pages/
    │   ├── index.vue             # Redireciona para a listagem
    │   ├── login.vue
    │   └── seguros/
    │       ├── index.vue         # Listagem + cards de resumo
    │       └── novo.vue          # Contratação de seguro
    ├── stores/                  # Pinia Stores
    │   ├── auth.ts
    │   ├── data.ts
    │   └── seguro.ts
    ├── tests/
    └── types/
```

---

## API Endpoints

### Autenticação
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/v1/auth/login` | Login |
| POST | `/api/v1/auth/logout` | Logout |
| POST | `/api/v1/auth/refresh` | Refresh token |
| GET | `/api/v1/auth/me` | Dados do usuário |

### Seguros
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/seguros` | Listar seguros |
| GET | `/api/v1/seguros/summary` | Resumo agregado por status de vigência (total, vigente, a vencer, vencido) |
| POST | `/api/v1/seguros` | Criar seguro |
| GET | `/api/v1/seguros/{id}` | Detalhes do seguro |
| PUT | `/api/v1/seguros/{id}` | Atualizar seguro |
| DELETE | `/api/v1/seguros/{id}` | Remover seguro |

### Dados Auxiliares
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/v1/seguradoras` | Listar seguradoras |
| GET | `/api/v1/ramos` | Listar ramos |
| GET | `/api/v1/cep/{cep}` | Buscar endereço |

### Parâmetros de Listagem
```
GET /api/v1/seguros?documento=12345678909
                   &status=vigente            # vigente | a_vencer | vencido
                   &inicio_vigencia_de=2024-01-01
                   &fim_vigencia_ate=2024-12-31
                   &seguradora_id=1
                   &ramo_id=1
                   &sort_by=valor_total
                   &sort_order=desc
                   &per_page=25
                   &page=1
```

---

## Testes

**Status atual:  52 testes backend / 43 testes frontend, todos passando.**

### Backend (PHPUnit)

Com Docker (recomendado):
```bash
docker-compose exec backend php vendor/bin/phpunit

# Apenas testes de feature
docker-compose exec backend php vendor/bin/phpunit tests/Feature

# Apenas testes unitários
docker-compose exec backend php vendor/bin/phpunit tests/Unit

# Um teste específico
docker-compose exec backend php vendor/bin/phpunit --filter SeguroTest
```

Sem Docker:
```bash
cd backend
php vendor/bin/phpunit
```

**Cobertura de Testes Backend:**
- Validação de CPF (múltiplos cenários)
- Validação de CNPJ (numérico e alfanumérico)
- Coerência financeira (incl. tolerância de arredondamento por parcela)
- Validação de vigência
- Autenticação (login, logout, refresh, token expirado/inválido)
- CRUD de seguros
- Filtros, ordenação e paginação
- Isolamento entre usuários (IDOR): um usuário não lê, altera nem exclui seguro de outro
- Filtro de listagem por status de vigência (inclusive rejeição de status inválido)
- Endpoint de resumo: contagens por status, isolamento por usuário e invalidação de cache

### Frontend (Vitest)

```bash
# Executar todos os testes
cd frontend
npm run test

# Executar com UI
npm run test:ui

# Executar com coverage
npm run test:coverage
```

**Cobertura de Testes Frontend:**
- Composable de validação de CPF/CNPJ
- Composable de formatação de moeda
- Composable de máscaras
- Componentes de input (MoneyInput, BaseSelect)

---

## Troubleshooting

### `npm ci` falha com erro de uso/ajuda
O `npm ci` exige o arquivo `package-lock.json`. Se ele não existir, use `npm install` no lugar (o Dockerfile já foi corrigido para isso).

### Erro `ERESOLVE could not resolve` ao rodar `npm install`
Conflito de versões entre `@nuxt/test-utils` e `vitest`. O `package.json` já foi corrigido com a versão compatível (`~3.12.0`). Se ainda ocorrer, delete `node_modules` e tente novamente:
```bash
cd frontend
rm -rf node_modules
npm install
```

### `failed to copy: connection reset by peer` no Docker
Erro de rede durante o pull de imagens. Basta rodar `docker-compose up -d --build` novamente, o Docker retoma de onde parou. Se usar proxy, verifique as configurações em **Docker Desktop → Settings → Resources → Proxies**.

### `php artisan jwt:secret` falha com "Class 'Tymon\\JWTAuth' not found"
O `package:discover` não foi executado. No Docker, isso é corrigido rodando:
```bash
docker-compose exec backend composer install --no-interaction
docker-compose exec backend php artisan jwt:secret
```

### Containers sobem mas backend retorna 500
Verifique se os passos pós-build foram executados:
```bash
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan jwt:secret
docker-compose exec backend php artisan migrate --seed
```
E confira os logs: `docker-compose logs backend`

### Rodar testes apagou os dados do banco de desenvolvimento
Isso acontecia porque o Docker injeta `DB_CONNECTION=mysql` no ambiente do container e o PHPUnit não sobrescreve variáveis de ambiente já existentes. O `phpunit.xml` já foi corrigido com `force="true"` (força sqlite em memória nos testes). Se restaurar o ambiente: `docker-compose exec backend php artisan migrate:fresh --seed --force`.

### `npm run lint` falha com "couldn't find a configuration file"
O projeto ainda não possui arquivo de configuração do ESLint (`.eslintrc`). O script existe no `package.json` para uso futuro; para habilitar, rode `npm init @eslint/config` ou instale `@nuxtjs/eslint-config`.

### Cuidado com sintaxe moderna no backend
O container roda **PHP 7.3** (por fidelidade à stack da Lojacorr). Recursos de PHP 7.4+ como arrow functions (`fn () => ...`), promoted properties e `?->` **não funcionam** e dão erro de parse/sintaxe. O mesmo vale para helpers introduzidos em Laravel 8+ (ex.: `DB::afterCommit`, `assertJsonPath`); a versão é **5.8**.

---

## Uso de IA no Desenvolvimento

Conforme solicitado, registro aqui o uso de ferramentas de IA durante o desenvolvimento:

### Ferramentas Utilizadas
- **Claude (Cursor AI / Claude Code)**: Assistente principal de desenvolvimento
- **GitHub Copilot (Claude)**: Rodada de revisão de qualidade, performance e refatorações

Exemplos reais de prompts de revisão estão em [docs/ai-interactions.md](docs/ai-interactions.md).

### Áreas de Utilização
1. **Estruturação do Projeto**: A IA auxiliou na definição da arquitetura em camadas e organização de pastas, seguindo padrões da comunidade Laravel.

2. **Validação de CPF/CNPJ**: O algoritmo de validação foi implementado com auxílio da IA, especialmente a nova regra de CNPJ alfanumérico de 2026.

3. **Configuração Docker**: O docker-compose.yml foi estruturado com auxílio da IA para garantir a correta comunicação entre serviços.

4. **Testes Automatizados**: A IA auxiliou na criação de casos de teste abrangentes para cobrir edge cases.

### Revisões Realizadas
Todas as sugestões da IA foram **revisadas e validadas** considerando:

- **Segurança**: Verificação de exposição de dados sensíveis, sanitização de inputs
- **Performance**: Análise de queries N+1, uso adequado de índices
- **Manutenibilidade**: Código limpo, separação de responsabilidades
- **Boas Práticas**: PSR-12 no PHP, ESLint no TypeScript

### Decisões Próprias
As seguintes decisões foram tomadas independentemente:
- Arquitetura em camadas do backend
- Escolha de Pinia sobre Vuex
- Estrutura dos composables
- Design da interface com Tailwind

---

## Melhorias Futuras

Se houvesse mais tempo, implementaria:

1. **Cobertura de Testes**: Aumentar para 80%+ em ambos os stacks
2. **CI/CD**: GitHub Actions para testes e deploy automatizado
3. **Documentação API**: Swagger/OpenAPI
4. **Notificações**: Sistema de alertas de vencimento
5. **Recuperação de senha**: Fluxo com token por e-mail
6. **Relatórios**: Exportação em PDF/Excel
7. **Multi-tenancy**: Suporte a múltiplas corretoras
8. **Internacionalização**: Suporte a múltiplos idiomas

---

## Autor

Desenvolvido com dedicação para o processo seletivo da **Lojacorr Seguros**.

---

## Licença

Este projeto é de uso exclusivo para avaliação técnica.
