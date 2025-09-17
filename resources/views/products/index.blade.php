<x-app-layout>
    <div class="max-w-7xl mx-auto p-6">
        @if(session('status'))
            <div class="mb-4 text-green-600">{{ session('status') }}</div>
        @endif

        <h1 class="text-2xl font-bold mb-4">Produits</h1>

        @if($products->isEmpty())
            <p>Aucun produit pour le moment.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($products as $p)
                    <div class="border rounded p-3">
                        <a href="{{ route('product.show',$p->slug) }}" class="font-semibold">
                            {{ $p->name }}
                        </a>
                        <div class="mt-2">
                            {{ number_format($p->price_cents/100, 2, ',', ' ') }} €
                        </div>
                        <form method="POST" action="{{ route('cart.add',$p) }}" class="mt-3">
                            @csrf
                            <button class="px-3 py-1 bg-blue-600 text-white rounded">
                                Ajouter au panier
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
</x-app-layout>