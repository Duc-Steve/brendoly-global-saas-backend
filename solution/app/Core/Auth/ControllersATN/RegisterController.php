<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\RegisterRequest;
use App\Core\Auth\ServicesATN\AuthService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller pour l'inscription complète des utilisateurs
 * Gère la création de compte, la génération de tokens et le retour du profil
 */
class RegisterController extends Controller
{
    use ApiResponseTrait; // Fournit successResponse(), errorResponse() et createdResponse() pour les réponses API standardisées

    public function __construct(
        private AuthService $authService, // Service de gestion de l'authentification et création d'utilisateur
    ) {}

    /**
     * Inscription d'un nouvel utilisateur
     * Valide les données, crée l'utilisateur, génère les tokens et retourne le profil
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated(); // Récupère les données validées depuis le formulaire

            // Crée l'utilisateur dans la base de données
            $this->authService->register($validated);

            // Retourne la réponse avec code 201 (created) // Retourne uniquement un message de succès
            return $this->successResponse(
                null, 
                'Inscription réussie. Vous pouvez maintenant vous connecter.', 
                201
            );

        } catch (Exception $e) {
            // Erreur serveur lors de l'inscription
            return $this->errorResponse('Erreur lors de l\'inscription'. $e->getMessage(), 500);
        }
    }
}
