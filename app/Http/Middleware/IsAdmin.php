<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use Closure;
use Illuminate\Http\Request;

class IsAdmin
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

        if (isset($user) && $user->is_admin == 1)
            return $next($request);

        return response('you are not admin');



    }
}
