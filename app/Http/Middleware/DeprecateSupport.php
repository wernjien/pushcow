<?php

namespace App\Http\Middleware;

use Closure;

class DeprecateSupport
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('uuid')) {
            $request->merge([
                'device_id' => $request->input('uuid'),
            ]);
        }

        return $next($request);
    }
}
