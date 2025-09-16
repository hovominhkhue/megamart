<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','provider','provider_ref','amount_cents','currency','status','paid_at','payload'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payload' => 'array', // JSON stocké en TEXT sous SQLite
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}