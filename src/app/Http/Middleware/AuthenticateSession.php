<?php

namespace Backpack\CRUD\app\Http\Middleware;

if (interface_exists('Illuminate\Contracts\Session\Middleware\AuthenticatesSessions', false)) {
    class AuthenticateSession extends AuthenticateSessionL9
    {
    }
} else {
    class AuthenticateSession
    {
        public function handle($request, \Closure $next)
        {
            return $next($request);
        }
    }
}
