<?php
// app/Http/Middleware/RouteTrackingMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RouteTrackingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get route information
        $route = $request->route();

        // Start tracking this request with a unique ID
        $requestId = uniqid();

        // Log route matching at the start
        Log::channel('daily')->info("ROUTE_TRACKING [$requestId] START: " . $request->method() . ' ' . $request->path(), [
            'route_name' => $route ? $route->getName() : 'unknown',
            'action' => $route ? $route->getActionName() : 'unknown',
            'uri' => $route ? $route->uri() : 'unknown'
        ]);

        // Process the request
        $response = $next($request);

        // Log completion
        Log::channel('daily')->info("ROUTE_TRACKING [$requestId] END: " . $request->method() . ' ' . $request->path(), [
            'status' => $response->getStatusCode()
        ]);

        return $response;
    }
}
