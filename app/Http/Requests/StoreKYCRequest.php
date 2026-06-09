<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKYCRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'civ' => 'required|string|in:M.,Mme',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'nat' => 'required|string|max:255',
            'dob' => 'required|date|before_or_equal:-21 years',
            'lieu_naiss' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'tel' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'piece' => 'required|string|in:CNI,Passeport,Carte Résident',
            'num_piece' => 'required|string|max:100',
            'expiration_piece' => 'required|date|after:today',
            'profession' => 'nullable|string|max:255',
            'employeur' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dob.before_or_equal' => 'Vous devez être âgé d\'au moins 21 ans pour procéder à l\'onboarding.',
            'expiration_piece.after' => 'La date d\'expiration doit être supérieure à la date du jour.',
        ];
    }
}
