<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiatWallet extends Model
{
    protected $fillable = [
        'user_id',
        'iban',
        'balance',
        'currency',
    ];
}