<?php

namespace App\Core\Auth\RequestsATN;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour la connexion
 * Valide les credentials (email ou téléphone) et le mot de passe
 * @package App\Core\Auth\Requests
 */
class LoginRequest extends FormRequest
{
    /**
     * Autorisation de la requête
     * Toutes les requêtes sont autorisées ici
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation
     * @return array
     */
    public function rules(): array
    {
        return [
            'credential' => 'required|string', // Peut être un email ou un téléphone
            'password' => 'required|string',   // Mot de passe requis
        ];
    }

    /**
     * Messages personnalisés pour la validation
     * @return array
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'L\'email ou le téléphone est requis.',
            'password.required' => 'Le mot de passe est requis.',
        ];
    }

    /**
     * Prétraitement des données avant la validation
     * Normalise email ou téléphone
     */
    protected function prepareForValidation(): void
    {
        $credential = trim($this->credential); // Supprime les espaces superflus
        
        if (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
            // Email : mettre en minuscules
            $credential = strtolower($credential);
        } else {
            // Téléphone : ne garder que les chiffres
            $credential = preg_replace('/[^0-9]/', '', $credential);
        }
        
        // Merge des données normalisées dans la requête
        $this->merge(['credential' => $credential]);
    }
}
