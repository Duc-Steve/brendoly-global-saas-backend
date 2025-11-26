<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\LoginRequest;
use App\Core\Auth\ServicesATN\AuthService;
use App\Core\Auth\ServicesATN\TokenService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller pour la connexion standard des utilisateurs
 * Gère la vérification des identifiants et la génération des tokens
 */
class LoginController extends Controller
{
    use ApiResponseTrait; // Fournit successResponse() et errorResponse() pour standardiser les réponses API

    public function __construct(
        private AuthService $authService, // Service d'authentification
        private TokenService $tokenService // Service pour gérer les tokens JWT ou API
    ) {}

    /**
     * Connexion utilisateur
     * Valide les identifiants et retourne les tokens + profil utilisateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated(); // Récupère les données validées (credential + password)
            
            // Vérifie les identifiants et retourne l'utilisateur si correct
            $user = $this->authService->login(
                $validated['credential'], 
                $validated['password']
            );

            if (!$user) {
                // Identifiants incorrects : retourne une erreur 401
                return $this->errorResponse('Identifiants incorrects', 401);
            }

            // Génère les tokens d'authentification (access + refresh)
            $tokens = $this->tokenService->createAuthTokens($user);

            // Crée cookie HttpOnly pour le token d'accès
            setcookie(
                'access_token',
                $tokens['access_token'],
                [
                    'expires' => time() + ($tokens['expires_in'] * 60),
                    'path' => '/',
                    'secure' => true,       // HTTPS obligatoire
                    'httponly' => true,     // JS ne peut pas lire
                    'samesite' => 'Strict', // Protège contre CSRF
                ]
            );

            // Refresh token dans un cookie séparé
            setcookie(
                'refresh_token',
                $tokens['refresh_token'],
                [
                    'expires' => time() + (30*24*60*60), // 30 jours
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]
            );

            // Récupère le profil de l'utilisateur
            $profile = $this->authService->getProfile($user);

            // Retour JSON **sans exposer le token**
            return $this->successResponse([
                'user' => $profile['user'],
                'tenant' => $profile['tenant'],
                'expires_in' => $tokens['expires_in'],
            ], 'Connexion réussie');

        } catch (Exception $e) {
            // Erreur serveur lors de la connexion
            return $this->errorResponse('Erreur lors de la connexion', 500);
        }
    }
}
