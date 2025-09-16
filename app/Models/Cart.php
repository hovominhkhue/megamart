<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','session_id','status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Total du panier (en centimes)
    public function subtotalCents(): int
    {
        return (int) $this->items->sum(fn ($it) => $it->qty * $it->unit_price_cents);
    }

    // Total formaté (euros)
    public function subtotalLabel(): string
    {
        return number_format($this->subtotalCents() / 100, 2, ',', ' ') . ' €';
    }
}