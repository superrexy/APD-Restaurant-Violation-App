<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;

test('success returns correct structure with default 200 status', function () {
    $data = ['id' => 1, 'name' => 'Test'];

    $response = (new Controller)->success($data);

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray([
        'statusCode' => 200,
        'message' => 'Success',
        'data' => $data,
        'meta' => [],
    ]);
});

test('success returns correct structure with custom message', function () {
    $data = ['id' => 1, 'name' => 'Test'];

    $response = (new Controller)->success($data, 'Custom message');

    expect($response->json())->toMatchArray([
        'statusCode' => 200,
        'message' => 'Custom message',
        'data' => $data,
        'meta' => [],
    ]);
});

test('success returns correct structure with custom status code', function () {
    $data = ['id' => 1];

    $response = (new Controller)->success($data, 'Created', 201);

    expect($response->status())->toBe(201);
    expect($response->json())->toMatchArray([
        'statusCode' => 201,
        'message' => 'Created',
        'data' => $data,
    ]);
});

test('success returns correct structure with meta data', function () {
    $data = ['id' => 1];
    $meta = ['timestamp' => '2026-02-14', 'version' => '1.0'];

    $response = (new Controller)->success($data, 'Success', 200, $meta);

    expect($response->json())->toMatchArray([
        'statusCode' => 200,
        'message' => 'Success',
        'data' => $data,
        'meta' => $meta,
    ]);
});

test('created returns 201 with correct structure', function () {
    $data = ['id' => 1, 'name' => 'New User'];

    $response = (new Controller)->created($data);

    expect($response->status())->toBe(201);
    expect($response->json())->toMatchArray([
        'statusCode' => 201,
        'message' => 'Success',
        'data' => $data,
        'meta' => [],
    ]);
});

test('created returns correct structure with custom message', function () {
    $data = ['id' => 1, 'name' => 'New User'];

    $response = (new Controller)->created($data, 'User created successfully');

    expect($response->status())->toBe(201);
    expect($response->json())->toMatchArray([
        'statusCode' => 201,
        'message' => 'User created successfully',
        'data' => $data,
    ]);
});

test('created returns correct structure with meta data', function () {
    $data = ['id' => 1, 'name' => 'New User'];
    $meta = ['created_at' => '2026-02-14'];

    $response = (new Controller)->created($data, 'Created', $meta);

    expect($response->json())->toMatchArray([
        'statusCode' => 201,
        'message' => 'Created',
        'data' => $data,
        'meta' => $meta,
    ]);
});

test('noContent returns 204 with no body', function () {
    $response = (new Controller)->noContent();

    expect($response->status())->toBe(204);
    expect($response->content())->toBeEmpty();
});

test('noContent returns 204 with custom message', function () {
    $response = (new Controller)->noContent('Resource deleted');

    expect($response->status())->toBe(204);
    expect($response->content())->toBeEmpty();
});

test('error returns correct structure with custom status code', function () {
    $response = (new Controller)->error('Something went wrong', 400);

    expect($response->status())->toBe(400);
    expect($response->json())->toMatchArray([
        'statusCode' => 400,
        'message' => 'Something went wrong',
        'data' => null,
        'meta' => [],
    ]);
});

test('error returns correct structure with data payload', function () {
    $errorData = ['field' => 'email', 'issue' => 'invalid'];

    $response = (new Controller)->error('Validation failed', 400, $errorData);

    expect($response->status())->toBe(400);
    expect($response->json())->toMatchArray([
        'statusCode' => 400,
        'message' => 'Validation failed',
        'data' => $errorData,
        'meta' => [],
    ]);
});

test('error returns correct structure with meta data', function () {
    $meta = ['timestamp' => '2026-02-14'];

    $response = (new Controller)->error('Error occurred', 500, null, $meta);

    expect($response->json())->toMatchArray([
        'statusCode' => 500,
        'message' => 'Error occurred',
        'data' => null,
        'meta' => $meta,
    ]);
});

test('unauthorized returns 401 with correct structure', function () {
    $response = (new Controller)->unauthorized();

    expect($response->status())->toBe(401);
    expect($response->json())->toMatchArray([
        'statusCode' => 401,
        'message' => 'Unauthorized',
        'data' => null,
        'meta' => [],
    ]);
});

test('unauthorized returns 401 with custom message', function () {
    $response = (new Controller)->unauthorized('Invalid credentials');

    expect($response->status())->toBe(401);
    expect($response->json())->toMatchArray([
        'statusCode' => 401,
        'message' => 'Invalid credentials',
        'data' => null,
    ]);
});

test('forbidden returns 403 with correct structure', function () {
    $response = (new Controller)->forbidden();

    expect($response->status())->toBe(403);
    expect($response->json())->toMatchArray([
        'statusCode' => 403,
        'message' => 'Forbidden',
        'data' => null,
        'meta' => [],
    ]);
});

test('forbidden returns 403 with custom message', function () {
    $response = (new Controller)->forbidden('You do not have permission');

    expect($response->status())->toBe(403);
    expect($response->json())->toMatchArray([
        'statusCode' => 403,
        'message' => 'You do not have permission',
        'data' => null,
    ]);
});

test('notFound returns 404 with correct structure', function () {
    $response = (new Controller)->notFound();

    expect($response->status())->toBe(404);
    expect($response->json())->toMatchArray([
        'statusCode' => 404,
        'message' => 'Not found',
        'data' => null,
        'meta' => [],
    ]);
});

test('notFound returns 404 with custom message', function () {
    $response = (new Controller)->notFound('User not found');

    expect($response->status())->toBe(404);
    expect($response->json())->toMatchArray([
        'statusCode' => 404,
        'message' => 'User not found',
        'data' => null,
    ]);
});

test('notFound includes resource name in message', function () {
    $response = (new Controller)->notFound(null, 'User');

    expect($response->status())->toBe(404);
    expect($response->json('message'))->toContain('User');
});

test('validationError returns 422 with correct structure and errors array', function () {
    $errors = [
        'email' => ['Email is required', 'Email must be valid'],
        'name' => ['Name is required'],
    ];

    $response = (new Controller)->validationError($errors);

    expect($response->status())->toBe(422);
    expect($response->json())->toMatchArray([
        'statusCode' => 422,
        'message' => 'Validation error',
        'data' => $errors,
        'meta' => [],
    ]);
});

test('validationError returns 422 with custom message', function () {
    $errors = ['email' => ['Invalid email']];

    $response = (new Controller)->validationError($errors, 'Invalid input');

    expect($response->status())->toBe(422);
    expect($response->json())->toMatchArray([
        'statusCode' => 422,
        'message' => 'Invalid input',
        'data' => $errors,
    ]);
});

test('paginate returns correct structure with pagination meta', function () {
    $items = [['id' => 1, 'name' => 'Item 1'], ['id' => 2, 'name' => 'Item 2']];
    $paginator = new Paginator($items, 10, 1);

    $response = (new Controller)->paginate($paginator);

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['statusCode', 'message', 'data', 'meta']);
    expect($response->json('statusCode'))->toBe(200);
    expect($response->json('message'))->toBe('Success');
});

test('paginate includes pagination fields in meta (current_page, per_page, total, last_page)', function () {
    $items = array_fill(0, 25, ['id' => 1]);
    $paginator = new Paginator($items, 10, 2);

    $response = (new Controller)->paginate($paginator);
    $meta = $response->json('meta');

    expect($meta)->toHaveKeys(['current_page', 'per_page', 'total', 'last_page']);
    expect($meta['current_page'])->toBe(2);
    expect($meta['per_page'])->toBe(10);
});

test('paginate returns correct structure with custom message', function () {
    $items = [['id' => 1]];
    $paginator = new Paginator($items, 10, 1);

    $response = (new Controller)->paginate($paginator, 'Users retrieved');

    expect($response->json())->toMatchArray([
        'statusCode' => 200,
        'message' => 'Users retrieved',
    ]);
});

test('paginate returns correct structure with additional meta data', function () {
    $items = [['id' => 1]];
    $paginator = new Paginator($items, 10, 1);
    $additionalMeta = ['timestamp' => '2026-02-14'];

    $response = (new Controller)->paginate($paginator, 'Success', $additionalMeta);
    $meta = $response->json('meta');

    expect($meta)->toHaveKey('timestamp');
    expect($meta['timestamp'])->toBe('2026-02-14');
});
