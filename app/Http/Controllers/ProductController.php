<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        $products = \App\Models\Product::latest()->paginate(12);
        return view('products.index', compact('products'));
    }

    public function show(string $slug) {
        $products = \App\Models\Product::where('slug', $slug)->firstOrFail();
        return view('products.show', compact('products'));
    }
}