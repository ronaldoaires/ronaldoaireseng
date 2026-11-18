<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Rules\Cpf;
use App\Services\MercadoPagoService;
use App\Services\PurchaseSyncService;
use App\Support\Phone;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CheckoutController extends Controller
{
    public function show(Request $request, Product $product): View|RedirectResponse
    {
        $user = $request->user();

        if ($redirect = $this->blocker($user, $product)) {
            return $redirect;
        }

        return view('site.checkout.show', [
            'product'    => $product,
            'isFree'     => $this->isFree($product),
            'cpf'        => Cpf::format($user->cpf),
            'phone'      => $user->phone,
            'needsTerms' => $user->terms_version !== config('legal.version'),
        ]);
    }

    public function store(Request $request, Product $product, MercadoPagoService $mp): RedirectResponse
    {
        $user = $request->user();

        if ($redirect = $this->blocker($user, $product)) {
            return $redirect;
        }

        $needsTerms = $user->terms_version !== config('legal.version');
        $isFree     = $this->isFree($product);

        $rules = [];

        // Paid products need the data Mercado Pago and the invoice ask for.
        if (! $isFree) {
            // Validate digits only; the mask characters are added back on save.
            $request->merge([
                'cpf'   => preg_replace('/\D/', '', (string) $request->input('cpf')),
                'phone' => preg_replace('/\D/', '', (string) $request->input('phone')),
            ]);

            $rules['cpf'] = [
                'required',
                new Cpf,
                // Uniqueness is checked on the hash because the stored CPF is encrypted.
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    $taken = User::where('cpf_hash', User::hashCpf($value))
                        ->whereKeyNot($user->id)
                        ->exists();

                    if ($taken) {
                        $fail('Este CPF já está em uso por outra conta.');
                    }
                },
            ];
            $rules['phone'] = ['required', 'digits_between:10,11'];
        }

        if ($needsTerms) {
            $rules['terms'] = ['accepted'];
        }

        $data = $request->validate($rules, [
            'cpf.required'         => 'Informe seu CPF.',
            'phone.required'       => 'Informe seu telefone.',
            'phone.digits_between' => 'Informe um telefone com DDD (10 ou 11 dígitos).',
            'terms.accepted'       => 'Para continuar, aceite os Termos de Uso, a Licença de Uso e a Política de Privacidade.',
        ]);

        $attributes = [];

        if (! $isFree) {
            $attributes['cpf']   = $data['cpf'];
            $attributes['phone'] = Phone::format($data['phone']);
        }

        // Proof of acceptance: when, and which version of the documents.
        if ($needsTerms) {
            $attributes['terms_accepted_at'] = now();
            $attributes['terms_version']     = config('legal.version');
        }

        if ($attributes) {
            $user->forceFill($attributes)->save();
        }

        // One row per user and product (unique index): a new attempt reuses the
        // row of an abandoned, failed or refunded one. A paid row is blocked above.
        $purchase = Purchase::firstOrNew([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        // Free product: released right away, no payment screen.
        if ($isFree) {
            $purchase->fill([
                'amount'         => 0,
                'status'         => 'paid',
                'source'         => Purchase::SOURCE_CHECKOUT,
                'payment_id'     => null,
                'payment_method' => 'free',
                'paid_at'        => now(),
            ])->save();

            return redirect()->route('account.library.show', ['product' => $product->slug])
                ->with('status', 'Pronto! ' . $product->title . ' já está na sua biblioteca.');
        }

        $purchase->fill([
            'amount'         => $product->price,
            'status'         => 'pending',
            'source'         => Purchase::SOURCE_CHECKOUT,
            'payment_id'     => null,
            'payment_method' => null,
            'paid_at'        => null,
        ])->save();

        try {
            $preference = $mp->createPreference($purchase, $user, $product);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'payment' => 'Não foi possível iniciar o pagamento agora. Tente novamente em instantes.',
            ]);
        }

        return redirect()->away($preference['init_point']);
    }

    /**
     * Where Mercado Pago sends the customer back after the payment screen.
     * The payment is read from Mercado Pago right now because the webhook may
     * arrive later (or not at all in a local environment).
     */
    public function finish(
        Request $request,
        Purchase $purchase,
        MercadoPagoService $mp,
        PurchaseSyncService $sync,
    ): View {
        abort_unless($purchase->user_id === $request->user()->id, 404);

        // payment_id comes in the query string, so it is only a hint: the payment
        // is fetched from Mercado Pago and its external_reference must match.
        $paymentId = (string) $request->query('payment_id');

        if (ctype_digit($paymentId) && ! $purchase->isPaid()) {
            try {
                $payment = $mp->getPayment($paymentId);

                if (($payment['external_reference'] ?? null) === 'purchase-' . $purchase->id) {
                    $sync->syncPayment($payment);
                    $purchase->refresh();
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        $purchase->load('product');

        return view('site.checkout.finish', ['purchase' => $purchase]);
    }

    private function isFree(Product $product): bool
    {
        return (float) $product->price <= 0;
    }

    /**
     * Where this user must go instead of the checkout, or null when they can proceed.
     */
    private function blocker(User $user, Product $product): ?RedirectResponse
    {
        abort_unless($product->isPublished(), 404);

        if ($user->delete_requested_at) {
            return redirect()->route('account.danger')
                ->with('status', 'Você pediu o encerramento da conta. Cancele o pedido para poder comprar.');
        }

        if ($user->owns($product)) {
            return redirect()->route('account.library.show', ['product' => $product->slug])
                ->with('status', 'Você já tem ' . $product->title . '. Está tudo na sua biblioteca.');
        }

        return null;
    }
}
