<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\LoginRequest;
use App\Core\Auth\ServicesATN\AuthService;
use App\Core\Auth\ServicesATN\TokenService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Exception;

/**
 * Controller pour la connexion standard des utilisateurs
 * Gère la vérification des identifiants et la génération des tokens
 * @var App\Core\Auth\ModelsATN\UserIdentify $user
 */

class LoginController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService,
        private TokenService $tokenService
    ) {}

    /**
     * Connexion utilisateur
     * Valide les identifiants et retourne les tokens + profil utilisateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
            $validated = $request->validated(); // 'credential' + 'password'

            $credential = $validated['credential'];
            $password = $validated['password'];

            // Vérifie les identifiants et retourne l'utilisateur si correct
            $user = $this->authService->login($credential, $password);

            if (! $user) {
                return $this->errorResponse('Identifiants incorrects', 401);
            }

            // Génère les tokens d'authentification (access + refresh)
            $tokens = $this->tokenService->createAuthTokens($user);

            // Récupère le profil utilisateur (user + tenant)
            $profile = $this->authService->getProfile($user);

            // Réponse de base (commune web/mobile)
            $payload = [
                'user' => $profile['user'],
                'tenant' => $profile['tenant'],
                'expires_in' => $tokens['expires_in'],
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
            ];

            // successResponse construit un JsonResponse standardisé (status 200)
            // Les cookies ont déjà été queued via Cookie::queue()
            return $this->successResponse($payload, 'Connexion réussie');

    }
}