<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\BankDetail;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected BankDetail $bankDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->product = Product::create([
            'libelle' => 'FCP Kori Sérénité',
            'description' => 'Fonds de test',
            'vl' => 10000.00,
            'seuil_minimum' => 50000.00,
            'is_active' => true,
        ]);

        $this->bankDetail = BankDetail::create([
            'bank_name' => 'Banque de Test',
            'iban' => 'BJ00 0000 0000',
            'rib' => '12345',
            'swift' => 'TESTBJ',
            'is_active' => true,
            'om_instructions' => 'OM step 1, step 2',
            'momo_instructions' => 'MoMo step 1, step 2',
            'bank_instructions' => 'Virement step 1, step 2',
        ]);
    }

    public function test_can_subscribe_manually_with_at_least_one_part(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/subscriptions', [
                'product_id' => $this->product->id,
                'nb_parts' => 1.0,
                'moyen_paiement' => 'orange_money',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'subscription' => [
                'id',
                'user_id',
                'product_id',
                'nb_parts',
                'prix_unitaire',
                'montant_total',
                'moyen_paiement',
                'statut',
                'reference_transaction',
            ],
            'pek_bank_details' => [
                'id',
                'bank_name',
                'om_instructions',
                'momo_instructions',
                'bank_instructions',
            ]
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'nb_parts' => 1.0,
            'moyen_paiement' => 'orange_money',
            'statut' => 'En attente',
        ]);
    }

    public function test_cannot_subscribe_with_less_than_one_part(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/subscriptions', [
                'product_id' => $this->product->id,
                'nb_parts' => 0.5,
                'moyen_paiement' => 'orange_money',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Le montant ou le nombre de parts est inférieur au minimum de souscription requis (1 part).');
    }
}
