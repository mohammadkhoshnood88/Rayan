<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ip;
use App\Services\IpService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    public function __construct()
    {
        auth()->setDefaultDriver('api');
    }

    public function login(Request $request)
    {
        $ip = $request->getClientIp();

        $user = Ip::where('ip' , $ip)->first();

        if (isset($user)){

            $token = auth()->login($user);
            return $this->respondWithToken($token);

        }

        return response('your ip not found');
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'code' => "200",
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL()
        ]);
    }

    public function store(Request $request , IpService $service)
    {
        return $service->store($request);
    }

}
