<?php

namespace App\Core\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request de validation pour la demande de mot de passe oublié
 * Vérifie que le credential fourni est un email ou un numéro de téléphone valide
 * et le prépare pour l'envoi du code de réinitialisation
 * @package App\Core\Auth\Requests
 */
class ForgotPasswordRequest extends FormRequest
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
     * Vérifie que le credential est une chaîne et qu'il correspond à un email ou un téléphone
     * @return array
     */
    public function rules(): array
    {
        return [
            'credential' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $normalized = $this->normalizeCredential($value);
                    
                    // Vérifie si c'est un email valide
                    $isEmail = filter_var($normalized, FILTER_VALIDATE_EMAIL) !== false;
                    
                    // Vérifie si c'est un téléphone valide (10 à 15 chiffres)
                    $isPhone = preg_match('/^[0-9]{10,15}$/', $normalized) === 1;
                    
                    if (!$isEmail && !$isPhone) {
                        $fail('Le credential doit être un email valide ou un numéro de téléphone.');
                    }
                },
            ],
        ];
    }

    /**
     * Messages personnalisés pour certaines règles
     * @return array
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'L\'email ou le téléphone est requis.',
            'credential.string' => 'Le credential doit être une chaîne de caractères.',
        ];
    }

    /**
     * Prétraitement des données avant la validation
     * Normalise le credential pour simplifier la validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('credential')) {
            $this->merge([
                'credential' => $this->normalizeCredential($this->credential)
            ]);
        }
    }

    /**
     * Normalise le credential
     * - Email : en minuscule
     * - Téléphone : supprime tous les caractères non numériques
     * @param string $credential
     * @return string
     */
    private function normalizeCredential(string $credential): string
    {
        $credential = trim($credential);
        
        if (str_contains($credential, '@')) {
            return strtolower($credential); // Email en minuscules
        }
        
        return preg_replace('/[^0-9]/', '', $credential); // Téléphone : chiffres uniquement
    }

    /**
     * Détermine si le credential est un email valide
     * @return bool
     */
    public function isEmailCredential(): bool
    {
        return filter_var($this->credential, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Détermine si le credential est un téléphone valide
     * @return bool
     */
    public function isPhoneCredential(): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $this->credential) === 1;
    }

    /**
     * Retourne le credential normalisé
     * @return string
     */
    public function getNormalizedCredential(): string
    {
        return $this->credential;
    }
}
