<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = ['name' , 'url' , 'method'];

    public function ip()
    {
        return $this->belongsToMany(Ip::class , 'ip_services' , 'service_id' , 'ip_id');
    }
}
