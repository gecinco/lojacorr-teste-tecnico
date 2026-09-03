<?php

namespace Tests\Feature;

use App\Models\Ramo;
use App\Models\Seguradora;
use App\Models\Seguro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeguroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->seguradora = Seguradora::create([
            'nome' => 'Seguradora Teste',
            'codigo' => 'TEST',
            'ativa' => true,
        ]);

        $this->ramo = Ramo::create([
            'nome' => 'Ramo Teste',
            'codigo' => 'RTEST',
            'ativo' => true,
        ]);

        $this->token = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->payloadSeguroValido = [
            'documento_segurado' => '12345678909',
            'nome_segurado' => 'João da Silva',
            'seguradora_id' => $this->seguradora->id,
            'ramo_id' => $this->ramo->id,
            'valor_total' => 1200.00,
            'quantidade_parcelas' => 12,
            'valor_parcela' => 100.00,
            'inicio_vigencia' => '2024-01-01',
            'fim_vigencia' => '2024-12-31',
            'cep' => '01310100',
            'logradouro' => 'Avenida Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ];
    }

    public function testCriaSeguroComDadosValidosCpf()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $this->payloadSeguroValido)
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Seguro contratado com sucesso',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'documento_segurado',
                    'nome_segurado',
                    'valor_total',
                    'status_vigencia',
                ],
            ]);
    }

    public function testCriaSeguroComDadosValidosCnpj()
    {
        $payload = array_merge($this->payloadSeguroValido, [
            'documento_segurado' => '11222333000181',
            'nome_segurado' => 'Empresa Teste LTDA',
            'valor_total' => 5000.00,
            'quantidade_parcelas' => 5,
            'valor_parcela' => 1000.00,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $payload)
            ->assertStatus(201);
    }

    public function testRejeitaSeguroComCpfInvalido()
    {
        $payload = array_merge($this->payloadSeguroValido, ['documento_segurado' => '12345678900']);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['documento_segurado']);
    }

    public function testRejeitaSeguroComIncoerenciaFinanceira()
    {
        $payload = array_merge($this->payloadSeguroValido, [
            'valor_parcela' => 50.00, // Deveria ser 100.00
        ]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['valor_parcela']);
    }

    public function testRejeitaSeguroComVigenciaInvalida()
    {
        $payload = array_merge($this->payloadSeguroValido, [
            'inicio_vigencia' => '2024-12-31',
            'fim_vigencia' => '2024-01-01', // Anterior ao início
        ]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fim_vigencia']);
    }

    public function testListaSegurosDoUsuarioAutenticado()
    {
        $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total', 'per_page'],
            ]);
    }

    public function testFiltraSegurosPorDocumento()
    {
        $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?documento=12345678909')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function testFiltraSegurosPorStatus()
    {
        $hoje = now()->startOfDay();
        $vigente = $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->subDays(5)->toDateString(),
            $hoje->copy()->addDays(5)->toDateString()
        );
        $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->subDays(2)->toDateString(),
            $hoje->copy()->subDay()->toDateString()
        ); // vencido
        $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->addDay()->toDateString(),
            $hoje->copy()->addDays(10)->toDateString()
        ); // a vencer

        $auth = $this->withHeader('Authorization', "Bearer {$this->token}");

        $vigentes = $auth->getJson('/api/v1/seguros?status=vigente')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->json('data');
        $this->assertEquals($vigente->id, $vigentes[0]['id']);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?status=vencido')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?status=a_vencer')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function testRejeitaStatusInvalido()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?status=qualquer')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testFiltraSegurosPorPeriodoDeVigencia()
    {
        $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?inicio_vigencia_de=2024-01-01&fim_vigencia_ate=2024-12-31')
            ->assertStatus(200);
    }

    public function testOrdenaSegurosPorColuna()
    {
        $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?sort_by=valor_total&sort_order=desc')
            ->assertStatus(200);
    }

    public function testPaginaResultados()
    {
        $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros?per_page=10&page=1')
            ->assertStatus(200)
            ->assertJson(['meta' => ['per_page' => 10]]);
    }

    public function testNaoPermiteAcessoASeguroDeOutroUsuario()
    {
        $seguro = $this->criarSeguroPara($this->user);

        $outroUsuario = User::create([
            'name' => 'Outro User',
            'email' => 'outro@example.com',
            'password' => Hash::make('password123'),
        ]);
        $tokenOutro = $this->postJson('/api/v1/auth/login', [
            'email' => 'outro@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$tokenOutro}")
            ->getJson("/api/v1/seguros/{$seguro->id}")
            ->assertStatus(404);

        $this->withHeader('Authorization', "Bearer {$tokenOutro}")
            ->deleteJson("/api/v1/seguros/{$seguro->id}")
            ->assertStatus(404);
    }

    public function testNaoPermiteAtualizarSeguroDeOutroUsuario()
    {
        $seguro = $this->criarSeguroPara($this->user);

        $outroUsuario = User::create([
            'name' => 'Outro User 2',
            'email' => 'outro2@example.com',
            'password' => Hash::make('password123'),
        ]);
        $tokenOutro = $this->postJson('/api/v1/auth/login', [
            'email' => 'outro2@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$tokenOutro}")
            ->putJson("/api/v1/seguros/{$seguro->id}", $this->payloadSeguroValido)
            ->assertStatus(404);

        // Garante que nada mudou.
        $this->assertDatabaseHas('seguros', [
            'id' => $seguro->id,
            'nome_segurado' => 'João da Silva',
        ]);
    }

    public function testSummaryRetornaContagensPorStatus()
    {
        // Janelas relativas à data de execução — não dependem do calendário.
        $hoje = now()->startOfDay();
        $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->subDays(5)->toDateString(),
            $hoje->copy()->addDays(5)->toDateString()
        ); // vigente
        $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->subDays(2)->toDateString(),
            $hoje->copy()->subDay()->toDateString()
        ); // vencido
        $this->criarSeguroPara(
            $this->user,
            $hoje->copy()->addDay()->toDateString(),
            $hoje->copy()->addDays(10)->toDateString()
        ); // a vencer

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros/summary')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['total']);
        $this->assertEquals(1, $data['vigente']);
        $this->assertEquals(1, $data['a_vencer']);
        $this->assertEquals(1, $data['vencido']);
    }

    public function testSummaryIsoladoPorUsuario()
    {
        $this->criarSeguroPara($this->user);

        $outroUsuario = User::create([
            'name' => 'Outro User 3',
            'email' => 'outro3@example.com',
            'password' => Hash::make('password123'),
        ]);
        $tokenOutro = $this->postJson('/api/v1/auth/login', [
            'email' => 'outro3@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$tokenOutro}")
            ->getJson('/api/v1/seguros/summary')
            ->assertStatus(200)
            ->assertJson(['data' => ['total' => 0]]);
    }

    public function testSummaryInvalidaCacheAposCriarSeguro()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros/summary')
            ->assertJson(['data' => ['total' => 0]]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/seguros', $this->payloadSeguroValido)
            ->assertStatus(201);

        // Sem invalidação, o cache responderia 0 mesmo após o INSERT.
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros/summary')
            ->assertJson(['data' => ['total' => 1]]);
    }

    public function testSummaryInvalidaCacheAposDeletarSeguro()
    {
        $seguro = $this->criarSeguroPara($this->user);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros/summary')
            ->assertJson(['data' => ['total' => 1]]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/seguros/{$seguro->id}")
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/seguros/summary')
            ->assertJson(['data' => ['total' => 0]]);
    }

    public function testRejeitaAcessoSemToken()
    {
        $this->getJson('/api/v1/seguros')
            ->assertStatus(401);
    }

    public function testRejeitaAcessoComTokenInvalido()
    {
        $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/seguros')
            ->assertStatus(401);
    }

    private function criarSeguroPara(User $user, ?string $inicio = null, ?string $fim = null): Seguro
    {
        return Seguro::create(array_merge($this->payloadSeguroValido, [
            'user_id' => $user->id,
            'tipo_documento' => 'cpf',
            'inicio_vigencia' => $inicio ?? $this->payloadSeguroValido['inicio_vigencia'],
            'fim_vigencia' => $fim ?? $this->payloadSeguroValido['fim_vigencia'],
        ]));
    }
}
