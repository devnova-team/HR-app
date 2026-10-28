<?php

use App\Support\ApiResponse;
use App\Support\ErrorCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Configure the application to render JSON responses for API requests or when the client expects JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Handle validation exceptions and return a structured JSON response with error details.
        $exceptions->render(function (ValidationException $e, Request $request) {
            $fields = collect($e->errors())->map(fn ($messages) => $messages[0])->all();

            return ApiResponse::error(ErrorCode::ValidationError, 'Invalid input', $fields);
        });

        // Handle authentication exceptions and return a structured JSON response indicating unauthenticated access.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return ApiResponse::error(ErrorCode::Unauthenticated, 'Unauthenticated');
        });

        // Handle HTTP exceptions and return structured JSON responses based on the status code.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            return match ($e->getStatusCode()) {
                403 => ApiResponse::error(ErrorCode::Forbidden, $e->getMessage() ?: 'Forbidden'),
                404 => ApiResponse::error(ErrorCode::NotFound, 'Resource not found'),
                409 => ApiResponse::error(ErrorCode::Conflict, $e->getMessage() ?: 'Conflict'),
                429 => ApiResponse::error(ErrorCode::TooManyRequests, 'Too many requests'),
                default => null,
            };
        });

        // Handle all other exceptions and return a generic error response for unexpected errors.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof HttpExceptionInterface || config('app.debug')) {
                return null;
            }

            return ApiResponse::error(ErrorCode::ServerError, 'Something went wrong');
        });
    })->create();
