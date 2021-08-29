<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoices;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Collection;
use Tymon\JWTAuth\Facades\JWTAuth;
use Zarinpal\Zarinpal;


class InvoiceController extends Controller
{

    public $loginAfterSignUp = true;

    public function __construct()
    {
        auth()->setDefaultDriver('api');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $payments = Invoices::where('ip' , $user->ip)->get();

        return InvoiceResource::collection($payments);
    }

    public function show(Request $request , $id)
    {
        $user = auth()->user();

        $payment = Invoices::where('id' , $id)->where('ip' , $user->ip)->first();

        return new InvoiceResource($payment);
    }
    
}
