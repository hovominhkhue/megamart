<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id','name','slug','price_cents','stock','cover_image','description',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug'; // on lie les produits par leur slug
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accesseur pratique (prix en euros, float)
    protected function priceEuro(): Attribute
    {
        return Attribute::get(fn () => $this->price_cents / 100);
    }

    // Formatage prêt à afficher (ex: "12,90 €")
    public function priceLabel(): string
    {
        return number_format($this->price_cents / 100, 2, ',', ' ') . ' €';
    }
}