<?php

namespace App\Core\Auth\TraitsATN;

use App\Core\Auth\ModelsATN\UserIdentify;

/**
 * Trait pour la gestion des tokens JWT/Sanctum
 * Fournit des méthodes pour créer, révoquer et gérer les tokens d'accès et de rafraîchissement.
 * @package App\Core\Auth\Traits
 */
trait TokenTrait
{
    /**
     * Crée un token d'accès pour l'utilisateur
     * Durée par défaut : 7 jours
     * @param User $user L'utilisateur pour lequel le token est créé
     * @param string $tokenName Nom du token (optionnel)
     * @return string Token en clair
     */
    protected function createAccessToken(UserIdentify $user, string $tokenName = 'auth_token')
    {
        return $user->createToken($tokenName, ['*'], now()->addDays(7))->plainTextToken;
    }

    /**
     * Crée un token de rafraîchissement
     * Durée par défaut : 30 jours
     * @param User $user L'utilisateur pour lequel le token est créé
     * @return string Token en clair
     */
    protected function createRefreshToken(UserIdentify $user)
    {
        return $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;
    }

    /**
     * Révoque tous les tokens de l'utilisateur
     * Utile pour déconnexion globale ou réinitialisation de sécurité
     * @param User $user
     */
    protected function revokeAllTokens(UserIdentify $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Révoque le token courant
     * Utile pour déconnexion du token utilisé actuellement
     * @param User $user
     */
    protected function revokeCurrentToken($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
