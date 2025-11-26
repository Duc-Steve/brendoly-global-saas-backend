<?php

namespace App\Core\Auth\RequestsATN;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour l'inscription utilisateur
 * Valide et prépare les données avant de créer un utilisateur et son entreprise
 * @package App\Core\Auth\Requests
 */
class RegisterRequest extends FormRequest
{
    /**
     * Autorisation de la requête
     * Ici, toutes les requêtes sont autorisées
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour chaque champ
     * @return array
     */
    public function rules(): array
    {
        return [
            // Informations personnelles
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:user_identifies,email', 
            'phone' => 'required|string|unique:user_identifies,phone', 
            'password' => 'required|string|min:8|confirmed',
            
            // Informations entreprise
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string|max:100',
            'company_sector' => 'required|string|max:350',
            'company_employees_number' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:100',
            'company_zipcode' => 'nullable|string|max:20',
            'company_country' => 'required|string|max:100',
        ];
    }

    /**
     * Messages personnalisés pour certaines règles
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }

    /**
     * Prétraitement des données avant la validation
     * Permet de normaliser email, téléphone et noms
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),               // Normalise l'email
            'phone' => preg_replace('/[^0-9]/', '', $this->phone),   // Supprime les caractères non numériques du téléphone
            'first_name' => trim($this->first_name),                 // Supprime espaces superflus
            'last_name' => trim($this->last_name),
        ]);
    }
}
