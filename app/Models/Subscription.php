<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($subscription) {
            $isCreatedAsSuccess = $subscription->wasRecentlyCreated && $subscription->statut === 'Succès';
            $isUpdatedToSuccess = !$subscription->wasRecentlyCreated && $subscription->wasChanged('statut') && $subscription->statut === 'Succès';

            if ($isCreatedAsSuccess || $isUpdatedToSuccess) {
                // 1. Dispatch confirmation email with receipt PDF
                try {
                    \App\Jobs\ProcessSubscriptionReceipt::dispatch($subscription);
                } catch (\Exception $e) {
                    \Log::error("Failed to dispatch ProcessSubscriptionReceipt on status update/create to Succes: " . $e->getMessage());
                }

                // 2. Create in-app notification
                try {
                    \App\Models\Notification::create([
                        'user_id' => $subscription->user_id,
                        'title' => 'Souscription Validée ✅',
                        'body' => "Votre souscription pour {$subscription->product->libelle} a été validée avec succès. Vos parts ont été créditées.",
                        'type' => 'success'
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to create in-app notification on subscription validation: " . $e->getMessage());
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'product_id',
        'nb_parts',
        'prix_unitaire',
        'montant_total',
        'moyen_paiement',
        'statut',
        'reference_transaction',
        'coolpay_transaction_ref',
    ];

    protected $casts = [
        'nb_parts' => 'decimal:8',
        'prix_unitaire' => 'decimal:4',
        'montant_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
