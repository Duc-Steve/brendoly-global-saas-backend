<?php

namespace App\Core\Auth\ServicesATN;

use App\Core\Auth\ModelsATN\UserIdentify;
use App\Core\Auth\TraitsATN\TokenTrait;
use Exception;

/**
 * Service de gestion des tokens d'authentification
 * Crée, rafraîchit et révoque les tokens (access et refresh)
 */
class TokenService
{
    use TokenTrait; // Fournit createAccessToken(), createRefreshToken(), revokeAllTokens(), revokeCurrentToken()

    /**
     * Crée les tokens d'authentification pour un utilisateur
     * Retourne access_token, refresh_token, type et durée de validité
     */
    public function createAuthTokens(UserIdentify $user): array
    {
        return [
            'access_token' => $this->createAccessToken($user),   // Token d'accès court terme
            'refresh_token' => $this->createRefreshToken($user), // Token long terme pour renouveler access_token
            'token_type' => 'Bearer',
            'expires_in' => 60 * 24 * 7, // Durée en minutes (7 jours)
        ];
    }

    /**
     * Rafraîchit le token d'accès
     * Supprime l'ancien refresh_token puis en génère de nouveaux
     */
    public function refreshToken(UserIdentify $user): array
    {
        // Révoque tous les anciens refresh_tokens pour sécurité
        $user->tokens()
            ->where('name', 'refresh_token')
            ->delete();

        // Génère de nouveaux tokens
        return $this->createAuthTokens($user);
    }

    /**
     * Déconnexion globale
     * Révoque tous les tokens de l'utilisateur
     */
    public function logout(UserIdentify $user): void
    {
        $this->revokeAllTokens($user);
    }

    /**
     * Déconnexion du device courant
     * Révoque uniquement le token utilisé pour la session en cours
     */
    public function logoutCurrentDevice(UserIdentify $user): void
    {
        $this->revokeCurrentToken($user);
    }
}
