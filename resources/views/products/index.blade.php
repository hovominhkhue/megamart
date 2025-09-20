<x-app-layout>
    <div class="max-w-7xl mx-auto p-6">
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

        <h1 class="text-2xl font-bold mb-4">Produits</h1>

        @if($products->isEmpty())
            <p>Aucun produit pour le moment.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($products as $p)
                    @php
                        $stock = (int)($p->stock ?? 0);
                        // même logique que show.blade.php
                        $img = $p->cover_image ?: 'https://picsum.photos/seed/'.$p->id.'/600/400';
                    @endphp

                    <div class="border rounded-lg bg-white overflow-hidden">
                        <a href="{{ route('product.show', $p) }}" class="block">
                            <!-- Image à taille uniforme -->
                            <div class="relative w-full h-48 md:h-56 lg:h-64 bg-gray-100 overflow-hidden">
                                <img
                                    src="{{ $img }}"
                                    alt="{{ $p->name }}"
                                    class="absolute inset-0 w-full h-full object-cover"
                                    loading="lazy" decoding="async"
                                    onerror="this.onerror=null; this.src='https://picsum.photos/seed/{{ $p->id }}/600/400';"
                                >
                            </div>
                        </a>

                        <div class="p-3 space-y-2">
                            <a href="{{ route('product.show', $p) }}" class="font-medium line-clamp-1">
                                {{ $p->name }}
                            </a>

                            <div class="text-sm text-gray-600">
                                {{ number_format($p->price_cents/100, 2, ',', ' ') }} €
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $stock > 0 ? "Stock : $stock" : 'Rupture' }}</span>
                            </div>

                            <button
                                type="button"
                                class="w-full px-3 py-2 rounded border text-sm add-to-cart-btn disabled:opacity-50"
                                data-url="{{ route('cart.add', $p) }}"
                                data-id="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-stock="{{ $stock }}"
                                @disabled($stock === 0)
                            >
                                {{ $stock === 0 ? 'Rupture' : 'Ajouter au panier' }}
                            </button>

                            <noscript>
                                <form method="POST" action="{{ route('cart.add', $p) }}" class="mt-2">
                                    @csrf
                                    <button class="w-full px-3 py-2 rounded bg-black text-white text-sm" @disabled($stock===0)>
                                        {{ $stock === 0 ? 'Rupture' : 'Ajouter au panier' }}
                                    </button>
                                </form>
                            </noscript>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-app-layout>