<?php

namespace App\Core\Auth\ServicesATN;

use App\Core\Auth\ModelsATN\RefreshTokens;
use App\Core\Auth\ModelsATN\UserIdentify;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Exception;

class TokenService
{
    /**
     * Crée les tokens JWT + refresh token pour un utilisateur
     */
    public function createAuthTokens(UserIdentify $user): array
    {
        $accessToken = $this->createAccessToken($user);
        $refreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken['token'],
            'token_type' => 'Bearer',
            // access_token expire dans JWT config (ex: 15 min, 1h, etc.)
            'expires_in' => config('jwt.ttl'), // en minutes
        ];
    }

    /**
     * Génère un JWT (access_token)
     */
    public function createAccessToken(UserIdentify $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Crée un refresh token unique, stocké en base
     */
    public function createRefreshToken(UserIdentify $user): array
    {
        $token = Str::uuid()->toString();
        $expiresAt = now()->addDays(30);

        $user->refreshTokens()->create([
            'id_refresh_token' => (string) Str::uuid(),
            'token' => $token,
            'expires_at' => $expiresAt, // CORRECTION ICI
        ]);

        return [
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Rafraîchit le token d’accès à partir d’un refresh_token valide
     */
    public function refreshToken(string $refreshToken): array
    {
        $record = RefreshTokens::where('token', $refreshToken)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            throw new Exception('Refresh token invalide ou expiré');
        }

        $user = $record->user;

        // 1. Supprimer l'ancien refresh token
        $record->delete();

        // 2. Créer un nouveau refresh token + access token
        return $this->createAuthTokens($user);
    }

    /**
     * Déconnexion globale → supprime tous les refresh_tokens
     */
    public function logout(UserIdentify $user): void
    {
        $user->refreshTokens()->delete();

        // Invalide le JWT actuel (optionnel)
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (Exception $e) {
            // ignore erreur si pas de JWT actif
        }
    }

    /**
     * Déconnexion du device courant → supprime seulement un refresh_token
     */
    public function logoutCurrentDevice(string $refreshToken): void
    {
        RefreshTokens::where('token', $refreshToken)->delete();

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (Exception $e) {}
    }
}