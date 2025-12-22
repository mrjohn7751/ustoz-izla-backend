<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UstozMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        if ($request->user()->role !== 'ustoz') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only ustozlar (teachers) can access this resource.'
            ], 403);
        }

        // Check if ustoz profile exists
        if (!$request->user()->ustoz) {
            return response()->json([
                'success' => false,
                'message' => 'Ustoz profile not found. Please complete your ustoz registration.'
            ], 403);
        }

        return $next($request);
    }
}
