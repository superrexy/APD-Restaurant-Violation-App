<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\Paginator;

class Controller
{
    /**
     * Return a standardized success response.
     */
    public function success($data, $message = 'Success', $statusCode = 200, $meta = [])
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $statusCode);
    }

    /**
     * Return a standardized created (201) response.
     */
    public function created($data, $message = 'Success', $meta = [])
    {
        return response()->json([
            'statusCode' => 201,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], 201);
    }

    /**
     * Return a 204 No Content response.
     */
    public function noContent($message = null)
    {
        return response()->noContent();
    }

    /**
     * Return a standardized error response.
     */
    public function error($message, $statusCode = 400, $data = null, $meta = [])
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $statusCode);
    }

    /**
     * Return a 401 Unauthorized response.
     */
    public function unauthorized($message = 'Unauthorized')
    {
        return response()->json([
            'statusCode' => 401,
            'message' => $message,
            'data' => null,
            'meta' => [],
        ], 401);
    }

    /**
     * Return a 403 Forbidden response.
     */
    public function forbidden($message = 'Forbidden')
    {
        return response()->json([
            'statusCode' => 403,
            'message' => $message,
            'data' => null,
            'meta' => [],
        ], 403);
    }

    /**
     * Return a 404 Not Found response.
     */
    public function notFound($message = null, $resource = null)
    {
        if ($message === null) {
            $message = $resource ? "{$resource} not found" : 'Not found';
        }

        return response()->json([
            'statusCode' => 404,
            'message' => $message,
            'data' => null,
            'meta' => [],
        ], 404);
    }

    /**
     * Return a 422 Validation Error response.
     */
    public function validationError($errors, $message = 'Validation error')
    {
        return response()->json([
            'statusCode' => 422,
            'message' => $message,
            'data' => $errors,
            'meta' => [],
        ], 422);
    }

    /**
     * Return a paginated response with pagination metadata.
     */
    public function paginate(Paginator $paginator, $message = 'Success', $meta = [])
    {
        $paginationMeta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => method_exists($paginator, 'total') ? $paginator->total() : count($paginator->items()),
            'last_page' => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : 1,
        ], $meta);

        return response()->json([
            'statusCode' => 200,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => $paginationMeta,
        ], 200);
    }
}
