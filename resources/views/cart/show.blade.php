<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Mon panier</h1>

    @if(($cart->items ?? collect())->isEmpty())
      <p>Votre panier est vide.</p>
      <a href="{{ route('home') }}" class="text-blue-600">Continuer vos achats</a>
    @else
      <div class="space-y-4">
        @php $total = 0; @endphp
        @foreach($cart->items as $item)
          @php
            // Fallback: si unit_price_cents est null, on prend le prix du produit
            $unit = $item->unit_price_cents ?? $item->product->price_cents;
            $line = $item->qty * $unit;
            $total += $line;
          @endphp

          <div class="border rounded p-4 flex items-center justify-between gap-4">
            <div>
              <div class="font-semibold">{{ $item->product->name }}</div>
              <div class="text-sm text-gray-600">
                Prix: {{ number_format($unit/100, 2, ',', ' ') }} €
              </div>
            </div>

            <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex items-center gap-2">
              @csrf
              @method('PATCH')
              <input type="number" name="qty" min="1" value="{{ $item->qty }}" class="border rounded px-2 py-1 w-20">
              <button class="px-3 py-1 bg-gray-200 rounded">Mettre à jour</button>
            </form>

            <div class="w-32 text-right font-semibold">
              {{ number_format($line/100, 2, ',', ' ') }} €
            </div>

            <form method="POST" action="{{ route('cart.remove', $item->product) }}">
              @csrf @method('DELETE')
              <button class="px-3 py-1 bg-red-600 text-white rounded">Supprimer</button>
            </form>
          </div>
        @endforeach
      </div>

      <div class="mt-6 flex items-center justify-between">
        <div class="text-xl font-bold">Total :
          {{ number_format($total/100, 2, ',', ' ') }} €
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
    @endif
  </div>
</x-app-layout>