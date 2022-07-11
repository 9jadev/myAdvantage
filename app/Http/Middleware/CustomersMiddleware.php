<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    
    public function handle(Request $request, Closure $next)
    {
        if (!$request->header('Authorization')) {
            return response()->json('Not Authorized', 401);
        }
        if (auth()->user()->tokenCan('role:customer')) {
            return $next($request);
        }

        return response()->json('Not Authorized', 401);

    }
}
