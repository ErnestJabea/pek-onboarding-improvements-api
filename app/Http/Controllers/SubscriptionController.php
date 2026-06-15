<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Product;
use App\Models\Notification;
use App\Models\BankDetail;
use App\Mail\SubscriptionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET'));
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'nb_parts' => 'required|numeric|min:0.0001',
            'moyen_paiement' => 'required|string',
            'montant_total' => 'nullable|numeric|min:0',
        ]);

        $product = Product::find($request->product_id);
        if (!$product || !$product->is_active) {
            return response()->json(['message' => 'Ce produit n\'est pas disponible à la souscription car il est désactivé.'], 422);
        }
        $montant_total = $request->montant_total ?? ($request->nb_parts * $product->vl);
        $final_amount = $montant_total + ($montant_total * 0.01);

        // Check onboarding status for 50k rule
        $onboardingStatus = Auth::user()->onboarding_status;

        if ($montant_total > 50000) {
            if ($onboardingStatus !== 'validated') {
                return response()->json([
                    'message' => 'Pour finaliser votre souscription de plus de 50 000 FCFA, vous devez fournir les informations restantes qui vous ont été demandées par mail.'
                ], 403);
            }
        }

        // Le minimum de placement est d'une part (valeur liquidative actuelle)
        if ($montant_total < ($product->vl - 0.01) || $request->nb_parts < 0.9999) {
            return response()->json(['message' => 'Le montant ou le nombre de parts est inférieur au minimum de souscription requis (1 part).'], 422);
        }

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'nb_parts' => $request->nb_parts,
            'prix_unitaire' => $product->vl,
            'montant_total' => $final_amount,
            'moyen_paiement' => $request->moyen_paiement,
            'statut' => 'En attente',
            'reference_transaction' => 'FCP-' . strtoupper(bin2hex(random_bytes(4))),
        ]);

        $clientSecret = null;
        $pekBankDetails = null;

        // 2. Handle Payment Logic
        // Bypassed automatic gateways since all subscriptions are manual requests now.
        $pekBankDetails = BankDetail::where('is_active', true)->first();

        // 3. Create In-App Notification
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Souscription enregistrée',
            'body' => "Votre demande de souscription pour {$product->libelle} ({$subscription->reference_transaction}) est en attente.",
            'type' => 'subscription'
        ]);

        // If onboarding is not validated and subscription is accepted (which means it's <= 50k), send a warning notification
        if ($onboardingStatus !== 'validated') {
            Notification::create([
                'user_id' => Auth::id(),
                'title' => '⚠️ Action requise pour validation',
                'body' => "Votre souscription pour {$product->libelle} ({$subscription->reference_transaction}) ne sera validée définitivement que si et seulement si vous renseignez les informations qui vous sont demandées par mail.",
                'type' => 'warning'
            ]);
        }

        // 4. Dispatch Background Job (Emails + PDF)
        \App\Jobs\ProcessSubscriptionReceipt::dispatch($subscription);

        return response()->json([
            'message' => 'Souscription enregistrée.',
            'subscription' => $subscription,
            'client_secret' => $clientSecret,
            'pek_bank_details' => $pekBankDetails,
        ]);
    }

    public function checkCoolPayStatus($id)
    {
        $subscription = Auth::user()->subscriptions()->with('product')->findOrFail($id);

        if ($subscription->statut === 'Succès') {
            return response()->json([
                'status' => 'success',
                'subscription' => $subscription,
                'message' => 'Cette transaction a déjà été confirmée avec succès.'
            ]);
        }

        if (!in_array($subscription->moyen_paiement, ['orange_money', 'mtn_momo'])) {
            return response()->json([
                'status' => $subscription->statut,
                'subscription' => $subscription,
                'message' => 'La vérification automatique n\'est disponible que pour les paiements Mobile Money.'
            ]);
        }

        $coolpayRef = $subscription->coolpay_transaction_ref;

        if (!$coolpayRef) {
            // Fallback s'il n'y a pas encore de référence CoolPay enregistrée (ex: anciennes transactions)
            return response()->json([
                'status' => $subscription->statut,
                'subscription' => $subscription,
                'message' => 'Aucune référence de transaction CoolPay trouvée pour cette souscription.'
            ], 400);
        }

        // Cas de simulation de test local
        if (str_starts_with($coolpayRef, 'MOCK_REF_')) {
            $subscription->update(['statut' => 'Succès']);


            return response()->json([
                'status' => 'success',
                'subscription' => $subscription->fresh('product'),
                'message' => 'Paiement de simulation validé avec succès !'
            ]);
        }

        $coolpayPublicKey = config('services.coolpay.public_key') ?? env('COOLPAY_PUBLIC_KEY');
        $url = "https://my-coolpay.com/api/{$coolpayPublicKey}/checkStatus/{$coolpayRef}";

        try {
            // Sécurité : Vérification SSL stricte exigée en production, désactivée uniquement en local
            $request = Http::timeout(10);
            if (!app()->environment('production')) {
                $request = $request->withoutVerifying();
            }
            $response = $request->get($url);
            $coolpayData = $response->json();

            \Log::info("CoolPay Manual Status Check Response for {$subscription->reference_transaction}: ", $coolpayData ?? []);

            if ($response->successful() && isset($coolpayData['status'])) {
                $status = strtolower($coolpayData['status']);
                if ($status === 'success' || $status === 'successful') {
                    $subscription->update(['statut' => 'Succès']);





                    return response()->json([
                        'status' => 'success',
                        'subscription' => $subscription->fresh('product'),
                        'message' => 'Paiement confirmé avec succès !'
                    ]);
                } elseif ($status === 'failed' || $status === 'failed_transaction' || $status === 'canceled') {
                    $subscription->update(['statut' => 'Échec']);
                    return response()->json([
                        'status' => 'failed',
                        'subscription' => $subscription->fresh('product'),
                        'message' => 'Le paiement a échoué ou a été annulé.'
                    ]);
                }
            }

            return response()->json([
                'status' => $subscription->statut,
                'subscription' => $subscription,
                'message' => 'Le paiement est toujours en attente de validation.'
            ]);

        } catch (\Exception $e) {
            \Log::error("Error checking CoolPay status: " . $e->getMessage());
            return response()->json([
                'status' => $subscription->statut,
                'subscription' => $subscription,
                'message' => 'Impossible de contacter la passerelle de paiement pour le moment.'
            ]);
        }
    }

    public function index()
    {
        return response()->json(Auth::user()->subscriptions()->with('product')->get());
    }
}
