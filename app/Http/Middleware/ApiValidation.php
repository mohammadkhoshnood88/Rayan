<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use App\Models\Services;
use Closure;
use Illuminate\Http\Request;

class ApiValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->getClientIp();

        $user = Ip::where('ip' , $ip)->first();

        $url = Services::where('url' , $request->path())
                            ->where('method' , $request->method())->first();

        if (isset($url) && in_array($request->path() , $user->services->pluck('url')->toArray()))
            return $next($request);
        else
            return response('url not found');
    }
}
