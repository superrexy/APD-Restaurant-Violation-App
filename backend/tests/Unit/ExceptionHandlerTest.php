<?php

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('401 unauthorized returns correct structure', function () {
    $exception = new \Illuminate\Auth\AuthenticationException('Unauthenticated');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->status())->toBe(401);
    expect($response->getOriginalContent())->toHaveKey('statusCode');
    expect($response->getOriginalContent())->toHaveKey('message');
    expect($response->getOriginalContent())->toHaveKey('data');
    expect($response->getOriginalContent())->toHaveKey('meta');
    expect($response->getOriginalContent()['statusCode'])->toBe(401);
    expect($response->getOriginalContent()['data'])->toBeNull();
    expect($response->getOriginalContent()['meta'])->toBeArray();
});

test('403 forbidden returns correct structure', function () {
    $exception = new AccessDeniedHttpException('You do not have permission');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->status())->toBe(403);
    expect($response->getOriginalContent())->toHaveKey('statusCode');
    expect($response->getOriginalContent())->toHaveKey('message');
    expect($response->getOriginalContent())->toHaveKey('data');
    expect($response->getOriginalContent())->toHaveKey('meta');
    expect($response->getOriginalContent()['statusCode'])->toBe(403);
    expect($response->getOriginalContent()['message'])->toBeString();
    expect($response->getOriginalContent()['data'])->toBeNull();
    expect($response->getOriginalContent()['meta'])->toBeArray();
});

test('404 not found returns correct structure', function () {
    $exception = new NotFoundHttpException('Resource not found');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->status())->toBe(404);
    expect($response->getOriginalContent())->toHaveKey('statusCode');
    expect($response->getOriginalContent())->toHaveKey('message');
    expect($response->getOriginalContent())->toHaveKey('data');
    expect($response->getOriginalContent())->toHaveKey('meta');
    expect($response->getOriginalContent()['statusCode'])->toBe(404);
    expect($response->getOriginalContent()['message'])->toContain('not found');
    expect($response->getOriginalContent()['data'])->toBeNull();
    expect($response->getOriginalContent()['meta'])->toBeArray();
});

test('422 validation error returns correct structure with errors', function () {
    $exception = ValidationException::withMessages([
        'email' => ['The email must be a valid email address.'],
        'name' => ['The name field is required.'],
    ]);

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->status())->toBe(422);
    expect($response->getOriginalContent())->toHaveKey('statusCode');
    expect($response->getOriginalContent())->toHaveKey('message');
    expect($response->getOriginalContent())->toHaveKey('data');
    expect($response->getOriginalContent())->toHaveKey('meta');
    expect($response->getOriginalContent()['statusCode'])->toBe(422);
    expect($response->getOriginalContent()['data'])->toBeArray();
    expect($response->getOriginalContent()['data'])->toHaveKey('email');
    expect($response->getOriginalContent()['data'])->toHaveKey('name');
    expect($response->getOriginalContent()['meta'])->toBeArray();
});

test('500 internal server error returns correct structure', function () {
    $exception = new Exception('Internal server error occurred');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->status())->toBe(500);
    expect($response->getOriginalContent())->toHaveKey('statusCode');
    expect($response->getOriginalContent())->toHaveKey('message');
    expect($response->getOriginalContent())->toHaveKey('data');
    expect($response->getOriginalContent())->toHaveKey('meta');
    expect($response->getOriginalContent()['statusCode'])->toBe(500);
    expect($response->getOriginalContent()['message'])->toBeString();
    expect($response->getOriginalContent()['data'])->toBeNull();
    expect($response->getOriginalContent()['meta'])->toBeArray();
});

test('error message verification for authentication exception', function () {
    $exception = new \Illuminate\Auth\AuthenticationException('User is not authenticated');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->getOriginalContent()['message'])->toContain('authenticated');
});

test('error message verification for authorization exception', function () {
    $exception = new AccessDeniedHttpException('Authorization failed');

    $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);

    expect($response->getOriginalContent()['message'])->toContain('Authorization');
});

test('all error responses have consistent structure', function () {
    $exceptions = [
        new \Illuminate\Auth\AuthenticationException('Unauthenticated'),
        new AccessDeniedHttpException('Forbidden'),
        new NotFoundHttpException('Not found'),
    ];

    foreach ($exceptions as $exception) {
        $response = app(\App\Exceptions\Handler::class)->render(request(), $exception);
        $content = $response->getOriginalContent();

        expect($content)->toHaveKey('statusCode');
        expect($content)->toHaveKey('message');
        expect($content)->toHaveKey('data');
        expect($content)->toHaveKey('meta');
        expect($content['data'])->toBeNull();
        expect($content['meta'])->toBeArray();
    }
});
