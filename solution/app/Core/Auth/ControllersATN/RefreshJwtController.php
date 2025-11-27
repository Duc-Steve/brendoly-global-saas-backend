<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class RefreshJwtController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private JWTAuth $jwt
    ) {}

    /**
     * Rafraîchit le token JWT
     * Le middleware jwt.refresh va automatiquement rafraîchir le token
     */
    public function refresh(): JsonResponse
    {
        try {
            // Après passage du middleware jwt.refresh, un nouveau token est généré
            $newToken = $this->jwt->parseToken()->refresh();
            
            // Récupère l'utilisateur authentifié
            $user = $this->jwt->user();
            
            return $this->successResponse([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 'Token rafraîchi avec succès');

        } catch (\Exception $e) {
            return $this->errorResponse('Impossible de rafraîchir le token', 401);
        }
    }

    /**
     * Déconnexion - Invalide le token
     */
    public function logout(): JsonResponse
    {
        try {
            $this->jwt->invalidate($this->jwt->getToken());
            
            return $this->successResponse([], 'Déconnexion réussie');

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la déconnexion', 500);
        }
    }
}