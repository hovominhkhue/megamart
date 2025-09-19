<x-app-layout>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Récapitulatif</h1>

    @if($errors->any())
      <div class="mb-4 text-red-600">{{ $errors->first() }}</div>
    @endif

    @php $totalLocal = 0; @endphp
    <div class="border rounded divide-y">
      @foreach($cart->items as $it)
        @php $line = $it->qty * $it->unit_price_cents; $totalLocal += $line; @endphp
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
        <div class="text-xl font-bold">{{ number_format($totalLocal/100, 2, ',', ' ') }} €</div>
      </div>
    </div>

    <div class="mt-6">
      <label class="block mb-2 font-semibold">Carte bancaire</label>
      <div id="card-element" class="border rounded p-3"></div>
      <div id="card-errors" class="text-red-600 mt-2"></div>

      <button id="pay-btn"
              class="mt-4 px-4 py-2 bg-green-600 text-white rounded disabled:opacity-50"
              data-total="{{ $total }}" data-currency="{{ $currency }}">
        Payer maintenant
      </button>
    </div>
  </div>

  <script src="https://js.stripe.com/v3"></script>
  <script>
    const stripe = Stripe(@json($stripeKey));
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const payBtn = document.getElementById('pay-btn');
    const errorsEl = document.getElementById('card-errors');

    async function createPaymentIntent() {
      const res = await fetch(@json(route('stripe.intent')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({}) // pas besoin d'envoyer le total: calcul côté serveur
      });
      if (!res.ok) {
        throw new Error('Impossible de créer le paiement');
      }
      return await res.json();
    }

    payBtn.addEventListener('click', async () => {
      payBtn.disabled = true;
      errorsEl.textContent = '';

      try {
        const { clientSecret, paymentIntentId } = await createPaymentIntent();

        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
          payment_method: { card }
        });

        if (error) {
          errorsEl.textContent = error.message || 'Erreur de paiement';
          payBtn.disabled = false;
          return;
        }

        // OK: on confirme côté serveur et on crée la commande
        const res = await fetch(@json(route('checkout.finalize')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({ payment_intent_id: paymentIntent.id })
        });

        if (res.redirected) {
          window.location = res.url; // va vers orders.show
        } else if (!res.ok) {
          const data = await res.json().catch(()=>null);
          errorsEl.textContent = (data && data.message) || 'Erreur de finalisation';
          payBtn.disabled = false;
        }
      } catch (e) {
        errorsEl.textContent = e.message || 'Erreur inattendue';
        payBtn.disabled = false;
      }
    });
  </script>
</x-app-layout>