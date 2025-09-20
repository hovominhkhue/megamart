<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Mon panier</h1>

    @if(session('status'))
      <div class="mb-4 px-3 py-2 rounded bg-green-600 text-white text-sm">{{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 px-3 py-2 rounded bg-red-600 text-white text-sm">{{ $errors->first() }}</div>
    @endif

    @if(($cart->items ?? collect())->isEmpty())
      <p>Votre panier est vide.</p>
      <a href="{{ route('products.index') }}" class="text-blue-600">← Continuer vos achats</a>
    @else
      @php $total = 0; @endphp

      <div class="space-y-4">
        @foreach($cart->items as $item)
          @php
            $unit = $item->unit_price_cents ?? $item->product->price_cents;
            $line = $item->qty * $unit;
            $total += $line;
            $stock = (int)($item->product->stock ?? 0);
          @endphp

          <div class="border rounded p-4 flex items-center justify-between gap-4 bg-white">
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ $item->product->name }}</div>
              <div class="text-sm text-gray-600">
                Prix : {{ number_format($unit/100, 2, ',', ' ') }} €
              </div>
              <div class="text-xs text-gray-500">
                Stock dispo : {{ $stock }}
              </div>
            </div>

            <!-- Contrôles quantité -->
            <div class="flex items-center gap-2">
              <!-- – : envoie qty-1 (ou 0 ⇒ suppression) -->
              <form method="POST" action="{{ route('cart.update', $item->product) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="qty" value="{{ max(0, $item->qty - 1) }}">
                <button class="w-8 h-8 border rounded flex items-center justify-center"
                        title="Diminuer">
                  –
                </button>
              </form>

              <!-- Affichage qty -->
              <span class="w-10 text-center">{{ $item->qty }}</span>

              <!-- + : envoie qty+1 (le contrôleur bloque si > stock) -->
              <form method="POST" action="{{ route('cart.update', $item->product) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="qty" value="{{ $item->qty + 1 }}">
                <button class="w-8 h-8 border rounded flex items-center justify-center"
                        title="Augmenter">
                  +
                </button>
              </form>
            </div>

            <div class="w-32 text-right font-semibold">
              {{ number_format($line/100, 2, ',', ' ') }} €
            </div>

            <!-- Supprimer (utilise route delete, mais update qty=0 marche aussi) -->
            <form method="POST" action="{{ route('cart.update', $item->product) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="qty" value="0">
              <button class="px-3 py-1 bg-red-600 text-white rounded">Supprimer</button>
            </form>
          </div>
        @endforeach
      </div>

      <div class="mt-6 flex items-center justify-between">
        <div class="text-xl font-bold">
          Total : {{ number_format($total/100, 2, ',', ' ') }} €
        </div>

        @auth
          <a href="{{ route('checkout.show') }}" class="px-4 py-2 bg-green-600 text-white rounded">
            Passer au paiement
          </a>
        @else
          <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
            Se connecter pour payer
          </a>
        @endauth
      </div>

      <div class="mt-4">
        <a href="{{ route('products.index') }}" class="text-sm text-gray-600 underline">← Continuer vos achats</a>
      </div>
    @endif
  </div>
</x-app-layout>