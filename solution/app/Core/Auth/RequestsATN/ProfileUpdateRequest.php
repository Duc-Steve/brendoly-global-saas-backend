<?php

namespace App\Core\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour la mise à jour du profil utilisateur
 * Valide uniquement les champs fournis et normalise email, téléphone et noms
 * @package App\Core\Auth\Requests
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Autorisation de la requête
     * L'utilisateur doit être connecté
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation
     * Les champs sont optionnels mais doivent respecter le format si fournis
     * @return array
     */
    public function rules(): array
    {
        $userId = auth()->id(); // ID de l'utilisateur connecté

        return [
            'first_name' => 'sometimes|string|max:100', // facultatif, chaîne max 100
            'last_name' => 'sometimes|string|max:100',
            'email' => "sometimes|email|unique:users,email,{$userId},id_user", // email unique sauf pour l'utilisateur courant
            'phone' => "sometimes|string|unique:users,phone,{$userId},id_user", // téléphone unique sauf pour l'utilisateur courant
        ];
    }

    /**
     * Prétraitement des données avant validation
     * Normalise email, téléphone et supprime les espaces superflus sur les noms
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email') && $this->email) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
        
        if ($this->has('phone') && $this->phone) {
            $this->merge(['phone' => preg_replace('/[^+0-9]/', '', $this->phone)]);
        }
        
        if ($this->has('first_name') && $this->first_name) {
            $this->merge(['first_name' => trim($this->first_name)]);
        }
        
        if ($this->has('last_name') && $this->last_name) {
            $this->merge(['last_name' => trim($this->last_name)]);
        }
    }
}
