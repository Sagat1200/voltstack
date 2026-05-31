<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Http\Middleware;

use Closure;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use Quantum\SpaBridge\Support\ProtocolVersion;

final class HandleSpaRequests implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $request = $request
            ->withAttribute('spa.request', $this->isSpaRequest($request))
            ->withAttribute('spa.target', $request->header('x-spa-target'))
            ->withAttribute('spa.version', $request->header('x-spa-version', ProtocolVersion::current()))
            ->withAttribute('spa.partials', $this->partials($request));

        return $next($request);
    }

    protected function isSpaRequest(Request $request): bool
    {
        return filter_var($request->header('x-spa', false), FILTER_VALIDATE_BOOL)
            || str_contains(strtolower((string) $request->header('accept', '')), 'application/json');
    }

    protected function partials(Request $request): array
    {
        $header = trim((string) $request->header('x-spa-partials', ''));

        if ($header === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $header))));
    }
}
