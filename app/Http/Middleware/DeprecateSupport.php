<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeprecateSupport
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
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
