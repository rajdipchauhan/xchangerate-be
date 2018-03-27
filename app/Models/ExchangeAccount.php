<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

class ExchangeAccount extends Model
{
    protected $fillable = [
        'user_id',
        'exchange_id',
        'name',
        'key',
        'secret'
    ];

    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, $user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        $query->where('user_id', $user);
    }

    public function scopeByExchange($query, $exchange)
    {
        if ($exchange instanceof Exchange) {
            $exchange = $exchange->id;
        }
        $query->where('exchange_id', $exchange);
    }

    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = encrypt($value);
    }

    public function getSecretAttribute()
    {
        return decrypt($this->attributes['secret']);
    }
}