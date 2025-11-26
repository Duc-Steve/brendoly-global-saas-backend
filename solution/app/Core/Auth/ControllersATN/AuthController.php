<?php

namespace App\Core\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Auth\Services\TokenService;
use App\Core\Auth\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * Controller pour les actions d'authentification globales
 * Gère la déconnexion, la récupération du profil et le rafraîchissement des tokens
 */
class AuthController extends Controller
{
    use ApiResponseTrait; // Fournit successResponse() et errorResponse() pour standardiser les réponses API

    public function __construct(
        private TokenService $tokenService // Service de gestion des tokens (JWT ou API)
    ) {}

    /**
     * Déconnexion de l'utilisateur
     * Supprime ou invalide les tokens existants
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->tokenService->logout($request->user()); // Invalide les tokens
            
            return $this->successResponse(null, 'Déconnexion réussie');
        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de la déconnexion', 500);
        }
    }

    /**
     * Récupère le profil de l'utilisateur connecté
     * Retourne également les informations du tenant si disponibles
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return $this->successResponse([
                'user' => [
                    'id' => $user->id_user,
                    'first_name' => $user->first_name, // Décrypté automatiquement
                    'last_name' => $user->last_name,   // Décrypté automatiquement
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_admin' => $user->is_admin,
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id_tenant,
                    'name' => $user->tenant->name, // Décrypté automatiquement
                ] : null
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération du profil', 500);
        }
    }

    /**
     * Rafraîchit le token d'accès
     * Retourne de nouveaux tokens (access et refresh)
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $tokens = $this->tokenService->refreshToken($request->user());
            
            return $this->successResponse($tokens, 'Token rafraîchi avec succès');
        } catch (Exception $e) {
            return $this->errorResponse('Erreur lors du rafraîchissement du token', 500);
        }
    }

    public function checkSession(): JsonResponse
    {
        $user = auth()->user(); // middleware auth.api doit être actif
        if ($user) {
            return response()->json(['authenticated' => true, 'user' => $user]);
        }
        return response()->json(['authenticated' => false], 401);
    }
}
