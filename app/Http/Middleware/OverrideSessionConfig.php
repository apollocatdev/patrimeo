<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Settings\VariousSettings;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class OverrideSessionConfig
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $sessionLifetime = VariousSettings::get()->sessionLifetime;
            $expireSessiononBrowserClose = VariousSettings::get()->expireSessionOnBrowserClose;

            if ($sessionLifetime) {
                Config::set('session.lifetime', $sessionLifetime);
                Config::set('session.expire_on_close', $expireSessiononBrowserClose);
            }
        }

        return $next($request);
    }
}
