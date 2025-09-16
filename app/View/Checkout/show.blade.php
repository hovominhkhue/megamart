<x-app-layout>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Récapitulatif</h1>

    @if($errors->any())
      <div class="mb-4 text-red-600">{{ $errors->first() }}</div>
    @endif

    @php $total = 0; @endphp
    <div class="border rounded divide-y">
      @foreach($cart->items as $it)
        @php $line = $it->qty * $it->unit_price_cents; $total += $line; @endphp
        <div class="p-4 flex items-center justify-between">
          <div>
            <div class="font-semibold">{{ $it->product->name }}</div>
            <div class="text-sm text-gray-600">x{{ $it->qty }}</div>
          </div>
          <div class="font-semibold">{{ number_format($line/100, 2, ',', ' ') }} €</div>
        </div>
      @endforeach
      <div class="p-4 flex items-center justify-between">
        <div class="font-semibold">Total</div>
        <div class="text-xl font-bold">{{ number_format($total/100, 2, ',', ' ') }} €</div>
      </div>
    </div>

    <form method="POST" action="{{ route('checkout.process') }}" class="mt-6">
      @csrf
      <button class="px-4 py-2 bg-green-600 text-white rounded">Confirmer et payer (démo)</button>
    </form>
  </div>
</x-app-layout>
