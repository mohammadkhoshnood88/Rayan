<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;



class Ip extends Authenticatable implements JWTSubject
{
    use HasFactory , Notifiable;

    protected $fillable = ['ip' , 'is_admin'];

    public function services()
    {
        return $this->belongsToMany(Services::class , 'ip_services' , 'ip_id' , 'service_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
