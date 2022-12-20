<?php

namespace Backpack\CRUD\app\Http\Middleware;

if (class_exists('Illuminate\Contracts\Session\Middleware\AuthenticatesSessions', false)) {
    class AuthenticateSession extends AuthenticateSessionL9
    {
    }
}
