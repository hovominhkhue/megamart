<x-app-layout>
  <div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Produits</h1>
    @isset($products)
      <ul class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($products as $p)
          <li class="border rounded p-3">
            <a href="{{ route('product.show',$p->slug) }}" class="font-semibold">{{ $p->name }}</a>
            <div>{{ number_format($p->price_cents/100, 2, ',', ' ') }} €</div>
          </li>
        @endforeach
      </ul>
      <div class="mt-4">{{ $products->links() }}</div>
    @endisset
  </div>
</x-app-layout>
