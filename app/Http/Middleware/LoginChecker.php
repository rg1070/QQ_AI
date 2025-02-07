<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is not logged in
        if (!Session::get('loggedIn')) {
            // You can customize the redirect route based on your application
            return redirect()->route('login');
        }

        return $next($request);
    }
}
