<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <a href="{{ route('products.index') }}" class="text-blue-600">&larr; Retour</a>

    <div class="grid md:grid-cols-2 gap-8 mt-4">
      <div class="border rounded p-4 bg-white">
        <img
          src="{{ $product->cover_image ?? 'https://picsum.photos/seed/'.$product->id.'/800/600' }}"
          alt="{{ $product->name }}"
          class="w-full h-auto rounded"
        >
      </div>

      <div>
        @if(session('status'))
          <div class="mb-4 px-3 py-2 rounded bg-green-600 text-white text-sm">
            {{ session('status') }}
          </div>
        @endif
        @if($errors->any())
          <div class="mb-4 px-3 py-2 rounded bg-red-600 text-white text-sm">
            {{ $errors->first() }}
          </div>
        @endif

        <h1 class="text-2xl font-bold">{{ $product->name }}</h1>

        <div class="flex items-center gap-3 mt-2">
          <span class="text-xl font-semibold">
            {{ number_format($product->price_cents/100, 2, ',', ' ') }} €
          </span>
          @php $stock = (int)($product->stock ?? 0); @endphp
          <span class="text-xs px-2 py-1 rounded border {{ $stock>0 ? 'border-green-600 text-green-700' : 'border-red-600 text-red-700' }}">
            {{ $stock > 0 ? "En stock : $stock" : 'Rupture' }}
          </span>
        </div>

        @if(!empty($product->description))
          <p class="mt-4 text-gray-700 leading-relaxed">
            {{ $product->description }}
          </p>
        @endif

        <!-- Actions -->
        <div class="mt-6 flex items-center gap-3">
          <!-- Quantité (optionnel pour AJAX : on peut lire sa valeur avant le fetch) -->
          <input
            type="number"
            name="qty"
            id="qty-input"
            min="1"
            value="1"
            class="border rounded px-2 py-1 w-24"
            @if($stock===0) disabled @endif
          >

          <!-- Bouton AJAX -->
          <button
            type="button"
            class="px-4 py-2 rounded border add-to-cart-btn disabled:opacity-50"
            data-url="{{ route('cart.add', $product) }}"
            data-id="{{ $product->id }}"
            data-name="{{ $product->name }}"
            data-stock="{{ $stock }}"
            data-qty-selector="#qty-input"
            @if($stock===0) disabled @endif
          >
            {{ $stock===0 ? 'Rupture' : 'Ajouter au panier' }}
          </button>

          <!-- Fallback sans JS -->
          <noscript>
            <form method="POST" action="{{ route('cart.add', $product) }}" class="inline">
              @csrf
              <input type="hidden" name="qty" value="1">
              <button class="px-4 py-2 rounded bg-black text-white" @if($stock===0) disabled @endif>
                {{ $stock===0 ? 'Rupture' : 'Ajouter au panier' }}
              </button>
            </form>
          </noscript>
        </div>

        <div class="mt-4">
          <a href="{{ route('products.index') }}" class="text-sm text-gray-600 underline">
            ← Retour à la boutique
          </a>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>