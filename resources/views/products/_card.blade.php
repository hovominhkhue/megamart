@if(!isset($wrap) || $wrap)
  <x-app-layout>
    <div class="max-w-7xl mx-auto p-6">
      @include('products._card', ['product' => $product, 'wrap' => false])
    </div>
  </x-app-layout>
@else
  @php $stock = (int)($product->stock ?? 0); @endphp

  <div class="border rounded-lg bg-white overflow-hidden">
    {{-- DEBUG: _card (no-img) --}}
    <div class="p-3 text-[10px] text-rose-600">_card.blade.php (no-img)</div>

    <div class="p-3 space-y-2">
      <a href="{{ route('product.show', $product) }}" class="font-medium line-clamp-2">
        {{ $product->name }}
      </a>

      <div class="text-sm text-gray-600">
        {{ number_format(($product->price_cents ?? 0)/100, 2, ',', ' ') }} €
      </div>

      <div class="flex items-center justify-between text-xs text-gray-500">
        <span>{{ $stock > 0 ? "Stock : $stock" : 'Rupture' }}</span>
      </div>

      <button
        type="button"
        class="relative z-10 w-full px-3 py-2 rounded border text-sm add-to-cart-btn disabled:opacity-50"
        data-url="{{ route('cart.add', $product) }}"
        data-id="{{ $product->id }}"
        data-name="{{ $product->name }}"
        data-stock="{{ $stock }}"
        @disabled($stock === 0)
      >
        {{ $stock === 0 ? 'Rupture' : 'Ajouter au panier' }}
      </button>
    </div>
  </div>
@endif//