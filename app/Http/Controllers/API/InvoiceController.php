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

        $payments = Invoices::where('ip', $user->ip)->get();

        return InvoiceResource::collection($payments);
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user();

        $payment = Invoices::where('id', $id)->where('ip', $user->ip)->first();

        return new InvoiceResource($payment);
    }

    public function request_pay(Request $request , $id)
    {
        $user = auth()->user();

        $total_price = 0;

        $payments = Invoices::find($id)->where('transaction_number', null)->latest()->first();

        if (isset($payments)) {
            $zarinpal = new Zarinpal('78253174-2cfc-4889-84fa-b47962db4cb6');

            $zarinpal->isZarinGate();

            $results = $zarinpal->request(
                "/response_pay/$id",
                $total_price, "desc", "emali@gmail.com", '09368816042');

            dd($results);

            if (isset($results['Authority'])) {
                file_put_contents('Authority', $results['Authority']);
                $a = $results['Authority'];
                return redirect("https://sandbox.zarinpal.com/pg/v4/payment/request.json");

                $zarinpal->redirect();

            }
        }

        return response('invoice not found');

    }

    public function response_pay($id)
    {
        $MerchantID = '78253174-2cfc-4889-84fa-b47962db4cb6';
        $Authority = \request()->get('Authority');

        $mobile = session()->get('mobile');

        $Amount = 0;
        $pay = PostCart::where('post_id', '=', $id)->where('mobile', '=', $mobile)
            ->where('invoice', '=', 0)->get();

        $post = Advertise::where('id', '=', $id)->first();


        foreach ($pay as $p) {
            $Amount = $Amount + option_price($p->option_name, $p->option_id);
        }

        $Amount = $Amount * 1000;
        // $Amount = 1000;


        //ما در اینجا مبلغ مورد نظر را بصورت دستی نوشتیم اما در پروژه های واقعی باید از دیتابیس بخوانیم
//        $Amount = $total_price;
        if (\request()->get('Status') == 'OK') {

            $option_id = [];

            $client = new \nusoap_client('https://sandbox.zarinpal.com/pg/v4/payment/verify.json', 'wsdl');
            $client->soap_defencoding = 'UTF-8';

            //در خط زیر یک درخواست به زرین پال ارسال می کنیم تا از صحت پرداخت کاربر مطمئن شویم
            $result = $client->call('PaymentVerification', [
                [
                    //این مقادیر را به سایت زرین پال برای دریافت تاییدیه نهایی ارسال می کنیم
                    'MerchantID' => $MerchantID,
                    'Authority' => $Authority,
                    'Amount' => $Amount,
                ],
            ]);

            $result['Status'] = 100;
            $idd = $id;
            if ($result['Status'] == 100) {

                $new_amount = new PostInvoice();
                $new_amount->post_id = $id;
                $transaction = rand(10000000, 99999999);
                $new_amount->transaction_number = $transaction;
                $new_amount->amount = $Amount;
                $new_amount->save();

                \session()->flash('factor_price', $Amount);


                foreach ($pay as $p) {


                    $a = false;

                    if ($p->option_name == "ladder") {
                        $a = $this->ladder($id, $post);
                    }
                    if ($p->option_name == "instagram") {
                        $a = $this->instagram($id, $post);
                    }
                    if ($p->option_name == "website") {
                        $a = $this->website($id, $post);
                    }

                    if ($p->option_name == "urgent") {
                        $a = $this->urgent($id, $post);
                    }

                    if ($p->option_name == "special") {
                        $a = $this->vip($id, $post);
                    }

                    if ($p->option_name == "re_active") {
                        $a = $this->re_active($id, $post);
                    }

                    if ($a) {
                        $p->invoice = $new_amount->id;
                        $p->post_id = $id;
                        $p->save();

                    }
                }

                \session()->put('pay_key', 'valid');
                \session()->flash('transaction', $transaction);

                return redirect("DriverPayment/post/$idd/factor");

            } else {
                \session()->put('pay_key', 'unvalid');
                \session()->flash('transaction', "");
                return redirect("DriverPayment/post/$idd/factor");
            }
        } else {
            \session()->put('pay_key', 'unvalid');
            \session()->flash('transaction', "");
            return redirect("DriverPayment/post/$id/factor");
        }


    }

    /*public function request_pay()
    {

        $data = array("merchant_id" => "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
            "amount" => 1000,
            "callback_url" => "http://www.yoursite.com/verify.php",
            "description" => "خرید تست",
            "metadata" => [ "email" => "info@email.com","mobile"=>"09121234567"],
        );
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);



        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                }
            } else {
                echo'Error Code: ' . $result['errors']['code'];
                echo'message: ' .  $result['errors']['message'];

            }
        }

    }*/

}
