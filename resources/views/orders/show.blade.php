<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    @if(session('status')) <div class="mb-4 text-green-600">{{ session('status') }}</div> @endif

    <a href="{{ route('orders.index') }}" class="text-blue-600">&larr; Retour</a>
    <h1 class="text-2xl font-bold mt-3">Commande #{{ $order->id }}</h1>
    <div class="text-sm text-gray-600 mb-4">
      Statut : {{ $order->status }} — Passée le {{ optional($order->placed_at)->format('d/m/Y H:i') }}
    </div>

    @php $total = 0; @endphp
    <div class="border rounded divide-y">
      @foreach($order->items as $it)
        @php $line = $it->qty * $it->unit_price_cents; $total += $line; @endphp
        <div class="p-4 flex justify-between">
          <div>{{ $it->product->name }} <span class="text-gray-600">x{{ $it->qty }}</span></div>
          <div class="font-semibold">{{ number_format($line/100, 2, ',', ' ') }} €</div>
        </div>
      @endforeach
      <div class="p-4 flex justify-between font-bold">
        <div>Total</div>
        <div>{{ number_format($total/100, 2, ',', ' ') }} €</div>
      </div>
    </div>
  </div>
</x-app-layout>