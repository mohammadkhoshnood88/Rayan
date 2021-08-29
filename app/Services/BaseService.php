<?php

namespace App\Services;

use Illuminate\Http\Request;

class BaseService
{
    protected $model;
    protected $resource;
    public function store($request)
    {
        $item = $this->model::create($request->all());
        return new $this->resource($item);
    }
}
