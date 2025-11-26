<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\ResetPasswordRequest;
use App\Core\Auth\ServicesATN\PasswordService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller pour la réinitialisation du mot de passe après vérification du code
 * @package App\Core\Auth\Controllers
 */
class ResetPasswordController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private PasswordService $passwordService
    ) {}

    /**
     * Réinitialise le mot de passe après vérification du code
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $credential = $validated['credential'];
            $code = $validated['code'];
            $password = $validated['password'];
            
            // Réinitialise le mot de passe
            $success = $this->passwordService->resetPassword($credential, $code, $password);

            if (!$success) {
                return $this->errorResponse(
                    'Code invalide, expiré ou credential incorrect. Veuillez recommencer la procédure.', 
                    400
                );
            }

            return $this->successResponse(
                [
                    'reset' => true,
                    'message' => 'Votre mot de passe a été réinitialisé avec succès'
                ],
                'Mot de passe réinitialisé avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Une erreur est survenue lors de la réinitialisation du mot de passe', 
                500
            );
        }
    }

    /**
     * Réinitialise le mot de passe pour un utilisateur authentifié avec code
     */
    public function resetForAuthenticatedUser(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $request->user();
            $code = $validated['code'];
            $password = $validated['password'];
            
            // Réinitialise le mot de passe avec code
            $success = $this->passwordService->changePasswordWithCode($user, $code, $password);

            if (!$success) {
                return $this->errorResponse(
                    'Code invalide ou expiré. Veuillez demander un nouveau code.', 
                    400
                );
            }

            return $this->successResponse(
                [
                    'reset' => true,
                    'message' => 'Votre mot de passe a été changé avec succès'
                ],
                'Mot de passe changé avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Une erreur est survenue lors du changement du mot de passe', 
                500
            );
        }
    }
}