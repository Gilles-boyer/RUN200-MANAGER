<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assigns a unique request ID to each request for tracing through logs.
 * The ID is available via request header and added to all log entries.
 */
class AssignRequestId
{
    public const HEADER_NAME = 'X-Request-ID';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use existing request ID if provided, otherwise generate one
        $requestId = $request->header(self::HEADER_NAME) ?? $this->generateRequestId();

        // Store in request for later use
        $request->headers->set(self::HEADER_NAME, $requestId);

        // Add to log context for all subsequent log calls
        $this->addToLogContext($requestId, $request);

        $response = $next($request);

        // Add request ID to response headers for client debugging
        $response->headers->set(self::HEADER_NAME, $requestId);

        return $response;
    }

    /**
     * Generate a unique request ID.
     */
    private function generateRequestId(): string
    {
        return Str::ulid()->toString();
    }

    /**
     * Add request context to all log entries.
     */
    private function addToLogContext(string $requestId, Request $request): void
    {
        $context = [
            'request_id' => $requestId,
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 100),
            'method' => $request->method(),
            'path' => $request->path(),
        ];

        // Add context to default log channel
        \Illuminate\Support\Facades\Log::shareContext($context);
    }
}
