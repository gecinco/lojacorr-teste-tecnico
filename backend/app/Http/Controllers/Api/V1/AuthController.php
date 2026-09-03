<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->errorResponse('Credenciais inválidas', 401);
        }

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 'Login realizado com sucesso');
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->successResponse(null, 'Logout realizado com sucesso');
    }

    public function refresh()
    {
        $result = $this->authService->refresh();

        return $this->successResponse([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 'Token atualizado com sucesso');
    }

    public function me()
    {
        $user = $this->authService->me();
        return $this->successResponse(new UserResource($user));
    }
}
