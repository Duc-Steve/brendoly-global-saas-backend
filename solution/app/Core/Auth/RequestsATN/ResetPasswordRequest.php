<?php

namespace App\Core\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour la réinitialisation du mot de passe après vérification du code
 * Valide :
 * - le credential (email ou téléphone)
 * - le code à 8 chiffres
 * - le nouveau mot de passe avec confirmation et règles de complexité
 * @package App\Core\Auth\Requests
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Autorisation de la requête
     * Toutes les requêtes sont autorisées
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
            'credential' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $normalized = $this->normalizeCredential($value);

                    $isEmail = filter_var($normalized, FILTER_VALIDATE_EMAIL) !== false;
                    $isPhone = preg_match('/^[0-9]{10,15}$/', $normalized) === 1;

                    if (!$isEmail && !$isPhone) {
                        $fail('Le credential doit être un email valide ou un numéro de téléphone.');
                    }
                },
            ],
            'code' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/', // assure que ce sont uniquement des chiffres
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed', // vérifie que password_confirmation correspond
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', // complexité
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }

    /**
     * Messages personnalisés pour les erreurs de validation
     * @return array
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'L\'email ou le téléphone est requis.',
            'credential.string' => 'Le credential doit être une chaîne de caractères.',

            'code.required' => 'Le code de vérification est requis.',
            'code.string' => 'Le code doit être une chaîne de caractères.',
            'code.size' => 'Le code doit contenir exactement 8 chiffres.',
            'code.regex' => 'Le code doit contenir uniquement des chiffres.',

            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.max' => 'Le mot de passe ne peut pas dépasser 255 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',

            'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'password_confirmation.string' => 'La confirmation doit être une chaîne de caractères.',
            'password_confirmation.min' => 'La confirmation doit contenir au moins 8 caractères.',
        ];
    }

    /**
     * Prétraitement des données avant validation
     * Normalise le credential, le code et supprime les espaces superflus sur les mots de passe
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('credential')) {
            $this->merge([
                'credential' => $this->normalizeCredential($this->credential),
            ]);
        }

        if ($this->has('code')) {
            $this->merge([
                'code' => preg_replace('/[^0-9]/', '', $this->code),
            ]);
        }

        if ($this->has('password')) {
            $this->merge([
                'password' => trim($this->password),
            ]);
        }

        if ($this->has('password_confirmation')) {
            $this->merge([
                'password_confirmation' => trim($this->password_confirmation),
            ]);
        }
    }

    /**
     * Normalise le credential
     * Email en minuscules, téléphone : chiffres uniquement
     */
    private function normalizeCredential(string $credential): string
    {
        $credential = trim($credential);

        if (str_contains($credential, '@')) {
            return strtolower($credential);
        }

        return preg_replace('/[^0-9]/', '', $credential);
    }

    /**
     * Vérifie si le credential est un email
     */
    public function isEmailCredential(): bool
    {
        return filter_var($this->credential, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Vérifie si le credential est un téléphone
     */
    public function isPhoneCredential(): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $this->credential) === 1;
    }

    /**
     * Retourne le credential normalisé
     */
    public function getNormalizedCredential(): string
    {
        return $this->credential;
    }

    /**
     * Retourne le code de vérification
     */
    public function getVerificationCode(): string
    {
        return $this->code;
    }

    /**
     * Retourne le nouveau mot de passe
     */
    public function getNewPassword(): string
    {
        return $this->password;
    }
}
