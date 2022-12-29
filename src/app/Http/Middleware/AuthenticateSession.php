<?php

namespace Backpack\CRUD\app\Http\Middleware;

if (app()->version() >= 9) {
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
