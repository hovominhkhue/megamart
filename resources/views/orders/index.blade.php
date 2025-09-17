<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Mes commandes</h1>

    @forelse($orders as $o)
      <a href="{{ route('orders.show', $o) }}" class="block border rounded p-4 mb-3 hover:bg-gray-50">
        <div class="flex justify-between">
          <div>#{{ $o->id }} — {{ $o->status }} — {{ optional($o->placed_at)->format('d/m/Y H:i') }}</div>
          <div class="font-semibold">{{ number_format($o->total_cents/100, 2, ',', ' ') }} €</div>
        </div>
      </a>
    @empty
      <p>Aucune commande pour le moment.</p>
    @endforelse

    <div class="mt-4">{{ $orders->links() }}</div>
  </div>
</x-app-layout>