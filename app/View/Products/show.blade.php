<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <a href="{{ route('home') }}" class="text-blue-600">&larr; Retour</a>

    <div class="grid md:grid-cols-2 gap-8 mt-4">
      <div class="border rounded p-4">
        <img src="{{ $product->cover_image ?? 'https://picsum.photos/seed/'.$product->id.'/800/600' }}"
             alt="{{ $product->name }}" class="w-full h-auto">
      </div>
      <div>
        <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
        <div class="text-xl mt-2">
          {{ number_format($product->price_cents/100, 2, ',', ' ') }} €
        </div>
        <div class="mt-2 text-sm text-gray-600">
          Stock : {{ $product->stock }}
        </div>
        <p class="mt-4">{{ $product->description }}</p>

        <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 flex items-center gap-3">
          @csrf
          <input type="number" name="qty" min="1" value="1" class="border rounded px-2 py-1 w-24">
          <button class="px-4 py-2 bg-blue-600 text-white rounded">Ajouter au panier</button>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
