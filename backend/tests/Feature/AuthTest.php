<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
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
    }

    public function testRealizaLoginComCredenciaisValidas()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function testRejeitaLoginComEmailInexistente()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciais inválidas',
            ]);
    }

    public function testRejeitaLoginComSenhaInvalida()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function testValidaCamposObrigatorios()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function testValidaFormatoDoEmail()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testRealizaLogoutComTokenValido()
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function testRejeitaLogoutSemToken()
    {
        $this->postJson('/api/v1/auth/logout')
            ->assertStatus(401);
    }

    public function testRetornaDadosDoUsuarioAutenticado()
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'test@example.com',
                    'name' => 'Test User',
                ],
            ]);
    }
}
