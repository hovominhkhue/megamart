<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Filtres & tri sécurisés via query string: ?q=..., ?category=slug, ?sort=price_asc|price_desc|new
        $validated = $request->validate([
            'q'        => ['nullable','string','max:120'],
            'category' => ['nullable','string','max:80'],
            'sort'     => ['nullable','in:new,price_asc,price_desc'],
        ]);

        $q        = $validated['q']        ?? null;
        $category = $validated['category'] ?? null;
        $sort     = $validated['sort']     ?? 'new';

        $products = Product::query()
            ->with([]) // ->with('category') si besoin d'afficher la catégorie
            ->when($q, fn($qb) =>
                $qb->where(function($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                })
            )
            ->when($category, fn($qb) =>
                $qb->whereHas('category', fn($c) => $c->where('slug', $category))
            )
            ->when($sort === 'new',        fn($qb) => $qb->orderByDesc('created_at'))
            ->when($sort === 'price_asc',  fn($qb) => $qb->orderBy('price_cents'))
            ->when($sort === 'price_desc', fn($qb) => $qb->orderByDesc('price_cents'))
            // ⬇️ IMPORTANT: on sélectionne cover_image pour avoir la même image que sur la page show
            ->select(['id','name','slug','price_cents','stock','cover_image','created_at'])
            ->paginate(12)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}