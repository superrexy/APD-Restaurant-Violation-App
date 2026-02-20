<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e)
    {
        // Always format JSON for API routes or requests without route context
        $isApi = false;
        $expectsJson = false;

        // If request has no route context (e.g., from unit tests), treat as API
        if ($request->route() === null) {
            $isApi = true;
        } else {
            try {
                $isApi = $request->is('api/*');
            } catch (\Exception $ignored) {
                // Fallback if is() fails for any reason
                $isApi = true;
            }
        }

        if (! $isApi) {
            try {
                $expectsJson = $request->expectsJson();
            } catch (\Exception $ignored) {
                // Ignore errors from expectsJson
            }
        }

        if ($isApi || $expectsJson) {
            return $this->renderApiException($e);
        }

        try {
            return parent::render($request, $e);
        } catch (\Exception $ignored) {
            // If parent render fails (e.g., no route context for redirects), fall back to API response
            return $this->renderApiException($e);
        }
    }

    protected function renderApiException(Throwable $e): JsonResponse
    {
        $statusCode = $this->getExceptionStatusCode($e);

        // Check original exception if this is a converted ModelNotFoundException
        $originalE = $e;
        if ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException) {
            $originalE = $e->getPrevious();
            $model = class_basename($originalE->getModel());
            $message = $model.' not found';
        } elseif ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            $message = $model.' not found';
        } else {
            $message = $e->getMessage() ?: $this->getDefaultMessage($statusCode);
        }

        $data = null;

        if ($e instanceof ValidationException) {
            $data = $e->errors();
        }

        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'meta' => [],
        ], $statusCode);
    }

    protected function getExceptionStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof AuthenticationException => 401,
            $e instanceof AccessDeniedHttpException => 403,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof NotFoundHttpException => 404,
            $e instanceof ValidationException => 422,
            $e instanceof HttpException => $e->getStatusCode(),
            default => 500,
        };
    }

    protected function getDefaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            401 => 'Unauthenticated',
            403 => 'Access denied',
            404 => 'Not found',
            422 => 'The given data was invalid',
            500 => 'Internal server error',
            default => 'An error occurred',
        };
    }
}
