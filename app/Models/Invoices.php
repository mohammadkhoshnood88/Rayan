<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;

    public function carts()
    {
        return $this->hasMany(Cart::class , 'invoice_id');
    }
}
