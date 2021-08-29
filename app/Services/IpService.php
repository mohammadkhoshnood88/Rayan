<?php

namespace App\Services;

use App\Models\Ip;
use Illuminate\Http\Request;

class IpService extends BaseService
{
    protected $model = Ip::class;
    protected $resource;

    public function store($request)
    {
        $item = $this->model::create($request->all());

        $item->services()->create(['name' => $request->name , 'url' => $request->url , 'method' => $request->method]);

        return $item;

        return new $this->resource($item);
    }

}
