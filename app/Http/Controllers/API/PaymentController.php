<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Invoices;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Invoices::all();
        return PaymentResource::collection($payments);
    }

    public function show($id)
    {
        $payments = Invoices::find($id);

        return new PaymentResource($payments);
    }

    public function change_status(Request $request , $id)
    {
        $payments = Invoices::find($id);

        $payments->status = $request->status;
        $payments->save();

        return new PaymentResource($payments);
    }
}
