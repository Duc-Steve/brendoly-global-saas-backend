<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\ServicesATN\AuthService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CheckSessionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Vérifie si la session JWT est valide et retourne le profil utilisateur
     */
    public function checkSession(): JsonResponse
    {
        try {
            // Avec php-open-source-saver/jwt-auth, l'utilisateur est déjà authentifié via le middleware
            $user = auth()->user();
            
            if (!$user) {
                return $this->errorResponse('Non authentifié', 401);
            }

            // Récupère le profil complet (user + tenant)
            $profile = $this->authService->getProfile($user);

            return $this->successResponse([
                'user' => $profile['user'],
                'tenant' => $profile['tenant'],
                'authenticated' => true
            ], 'Session valide');

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur de vérification de session', 500);
        }
    }
}