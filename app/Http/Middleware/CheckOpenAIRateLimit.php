<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckOpenAIRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip rate limiting for GET requests (except those that trigger API calls)
        if ($request->method() === 'GET') {
            return $next($request);
        }

        // Check if this is an OpenAI embedding request
        if ($request->is('embedding/*')) {
            $maxRequestsPerMinute = 60;
            $requests = Cache::get('openai_user_requests_' . $request->ip(), []);
            
            // Clean old requests (older than 1 minute)
            $now = time();
            $requests = array_filter($requests, function ($timestamp) use ($now) {
                return ($now - $timestamp) < 60;
            });
            
            // Check rate limit
            if (count($requests) >= $maxRequestsPerMinute) {
                return back()
                    ->withInput()
                    ->with('error', 'Too many requests. Please wait a minute before trying again.')
                    ->with('retry_after', 60);
            }
            
            // Add current request
            $requests[] = $now;
            Cache::put('openai_user_requests_' . $request->ip(), $requests, now()->addMinutes(2));
        }

        return $next($request);
    }
}