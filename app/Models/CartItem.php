<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id','product_id','qty','unit_price_cents'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotalCents(): int
    {
        return (int) ($this->qty * $this->unit_price_cents);
    }

    public function lineTotalLabel(): string
    {
        return number_format($this->lineTotalCents() / 100, 2, ',', ' ') . ' €';
    }
}