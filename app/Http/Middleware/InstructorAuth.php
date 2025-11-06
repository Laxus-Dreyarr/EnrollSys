<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('instructor')->check()) {
            return redirect('/instructor')->with('error', 'Please login to access this page.');
        }

        return $next($request);
    }
}
