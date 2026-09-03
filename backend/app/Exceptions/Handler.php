<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException($request, Exception $exception)
    {
        $statusCode = 500;
        $message = 'Erro interno do servidor';
        $errors = null;

        if ($exception instanceof ValidationException) {
            $statusCode = 422;
            $message = 'Dados inválidos';
            $errors = $exception->errors();
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = 'Recurso não encontrado';
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = 'Rota não encontrada';
        } elseif ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'Não autenticado';
        } elseif ($exception instanceof TokenExpiredException) {
            $statusCode = 401;
            $message = 'Token expirado';
        } elseif ($exception instanceof TokenInvalidException) {
            $statusCode = 401;
            $message = 'Token inválido';
        } elseif ($exception instanceof JWTException) {
            $statusCode = 401;
            $message = 'Token não fornecido';
        } elseif ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage() ?: 'Erro HTTP';
        }

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // API stateless: sempre 401 JSON (não há rota de login web)
        return response()->json([
            'success' => false,
            'message' => 'Não autenticado'
        ], 401);
    }
}
