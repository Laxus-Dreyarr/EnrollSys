<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // Get API key from header or query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'help' => 'Provide API key in header: X-API-Key or query parameter: api_key'
            ], 401);
        }

        // Check cache first
        $cacheKey = 'api_key_' . hash('sha256', $apiKey);
        $isValid = Cache::remember($cacheKey, 300, function () use ($apiKey) {
            // Check against stored hash
            $storedKey = DB::table('api_keys')
                ->where('key', hash('sha256', $apiKey))
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            return $storedKey !== null;
        });

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API key'
            ], 401);
        }

        // Log API access (optional)
        DB::table('api_access_logs')->insert([
            'api_key' => substr(hash('sha256', $apiKey), 0, 16), // Store partial hash for logging
            'ip_address' => $request->ip(),
            'endpoint' => $request->path(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Rate limiting
        $rateLimitKey = 'api_rate_' . hash('sha256', $apiKey) . '_' . now()->format('Y-m-d-H');
        $requests = Cache::get($rateLimitKey, 0);
        
        if ($requests > 100) { // 100 requests per hour
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Maximum 100 requests per hour.'
            ], 429);
        }
        
        Cache::put($rateLimitKey, $requests + 1, 3600);

        return $next($request);
    }
}