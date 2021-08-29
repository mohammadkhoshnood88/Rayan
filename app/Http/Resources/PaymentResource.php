<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ip' => $this->ip,
            'transaction number' => $this->transaction_number,
            'amount' => $this->amount,
            'status' => ($this->status ? 'approved' : 'not approved'),
            'date' => Carbon::parse($this->created_at)->format('D , h:m')
        ];
    }
}
