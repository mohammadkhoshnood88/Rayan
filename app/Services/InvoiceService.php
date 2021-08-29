<?php

namespace App\Services;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoices;
use Illuminate\Http\Request;

class InvoiceService extends BaseService
{
    protected $model = Invoices::class;
    protected $resource = InvoiceResource::class;
}
