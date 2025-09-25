<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'payment_intent',
        'session_id',
        'customer_email',
        'status',
        'currency',
        'amount',
    ];
}
