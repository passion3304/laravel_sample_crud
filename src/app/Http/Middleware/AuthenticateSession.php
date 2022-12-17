<?php

namespace Backpack\CRUD\app\Http\Middleware;

if (class_exists('Illuminate\Contracts\Session\Middleware\AuthenticatesSessions', false)) {
    class AuthenticateSession extends AuthenticateSessionL8 implements \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions
    {
    }
} else {
    class AuthenticateSession extends AuthenticateSessionL8
    {
    }
}
