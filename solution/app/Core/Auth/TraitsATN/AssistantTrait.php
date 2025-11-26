<?php

namespace App\Core\Auth\TraitsATN;

use Illuminate\Support\Str;

/**
 * Trait avec des méthodes utilitaires pour l'authentification
 * Fournit des fonctions réutilisables pour générer des codes, normaliser et valider des credentials
 * @package App\Core\Auth\Traits
 */
trait AssistantTrait
{
    /**
     * Génère un code de vérification à 8 chiffres
     * @return string Code de 8 chiffres, complété par des zéros si nécessaire
     */
    protected function generateVerificationCode(): string
    {
        // random_int est sécurisé pour les nombres aléatoires cryptographiques
        return str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Normalise une credential (email ou téléphone)
     * @param string $credential Email ou numéro de téléphone
     * @return string Credential normalisée
     */
    protected function normalizeCredential(string $credential): string
    {
        // Supprime les espaces et met en minuscules
        $credential = strtolower(trim($credential));
        
        // Si c'est un email, on le retourne tel quel
        if (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
            return $credential;
        }
        
        // Si c'est un téléphone, on supprime tous les caractères non numériques
        return preg_replace('/[^0-9]/', '', $credential);
    }

    /**
     * Vérifie si la credential est un email valide
     * @param string $credential Credential à tester
     * @return bool True si c'est un email, false sinon
     */
    protected function isEmail(string $credential): bool
    {
        return filter_var($credential, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Vérifie si la credential est un numéro de téléphone valide
     * @param string $credential Credential à tester
     * @return bool True si c'est un téléphone valide (10 à 15 chiffres), false sinon
     */
    protected function isPhone(string $credential): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $credential) === 1;
    }
}
