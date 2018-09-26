<?php

namespace Sota\System\Middleware;

use Closure;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->bound('sentry')) {
            
            $sentry = app('sentry');

            if (auth()->check()) {
                $user = auth()->user();
                $sentry->user_context([
                    'id' => $user->id,
                    'name' => $user->first_name.' '.$user->last_name,
                    'email' => $user->email,
                    'username' => $user->username
                ]);
            }
        }

        return $next($request);
    }
}