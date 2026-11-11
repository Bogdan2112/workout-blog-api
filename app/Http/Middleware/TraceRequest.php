<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TraceRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('TraceRequest: inainte', [
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        $response = $next($request);

        Log::info('TraceRequest: dupa', [
            'status' => $response->getStatusCode(),
        ]);

        // return $next($request);
        return $response;
    }
}
