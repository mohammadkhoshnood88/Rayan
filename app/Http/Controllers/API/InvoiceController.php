<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Cart;
use App\Models\Invoices;
use App\Services\InvoiceService;
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

        $payments = Invoices::where('ip', $user->ip)->get();

        return InvoiceResource::collection($payments);
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user();

        $payment = Invoices::where('id', $id)->where('ip', $user->ip)->first();

        return new InvoiceResource($payment);
    }

    public function store(Request $request, InvoiceService $service)
    {
        return $service->store($request);
    }

    public function store_cart(Request $request)
    {
        $ip = $request->getClientIp();

        $invoice = Invoices::where('ip', $ip)
            ->where('transaction_number', null)->first();
        if (empty($invoice))
            $invoice = Invoices::create(['ip' => $ip]);

        $request['ip'] = $ip;
        $invoice->carts()->create($request->all());

        return response()->json(['success' => true, 'data' => $invoice->carts]);

    }

    public function request_pay(Request $request)
    {
        list($amount , $invoice , $error) = $this->amount($request);

        if ($error != "")
            return response($error);

        $zarinpal = new Zarinpal('78253174-2cfc-4889-84fa-b47962db4cb6');

        $zarinpal->isZarinGate();
        $zarinpal->enableSandbox();

        $id = $invoice->id;

        $results = $zarinpal->request(
            "http://127.0.0.1:8000/api/v1/verify/pay/$id",
            $amount, "desc", "emali@gmail.com", '09368816042');

        if (isset($results['Authority'])) {

            dd($zarinpal->redirectUrl());

        }
    }

    public function verify_pay(Request $request)
    {

        list($amount , $invoice) = $this->amount($request);
        $Authority = $_GET['Authority'];

        $zarinpal = new Zarinpal('78253174-2cfc-4889-84fa-b47962db4cb6');
        $zarinpal->isZarinGate();
        $zarinpal->enableSandbox();
        $verify = $zarinpal->verify($amount , $Authority);

        if ($verify['stauts'] = 'success'){
            $invoice->amount = $amount;
            $invoice->transaction_number = random_int(100000 , 9999999);
            $invoice->save();
        }

        return new InvoiceResource($invoice);

    }

    private function amount(Request $request): array
    {
        $ip = $request->getClientIp();

        $invoice = Invoices::where('ip', $ip)
            ->where('transaction_number', null)->first();

        $error = "";
        if (empty($invoice)) {
            $error = 'factor doesnt exist';
            return array(0, [] , $error);
        }

        $amount = 0;
        foreach ($invoice->carts as $cart) {
            $amount += $cart->price;
        }

        return array($amount, $invoice , $error);
    }
}
