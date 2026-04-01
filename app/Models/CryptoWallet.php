<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoWallet extends Model
{
    protected $fillable = [
        'user_id',
        'cryptocurrency_id',
        'wallet_address',
        'balance',
    ];
}