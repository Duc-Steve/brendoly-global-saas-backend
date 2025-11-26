<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\ForgotPasswordRequest;
use App\Core\Auth\ServicesATN\PasswordService;
use App\Core\Auth\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller pour la demande de mot de passe oublié
 * Gère l'envoi du code de vérification par email ou SMS
 * @package App\Core\Auth\Controllers
 */
class ForgotPasswordController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private PasswordService $passwordService
    ) {}

    /**
     * Envoie un code de vérification pour la réinitialisation du mot de passe
     */
    public function sendResetCode(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $credential = $validated['credential'];
            
            // Détermine le type de credential pour la réponse
            $isEmail = $request->isEmailCredential();
            $credentialType = $isEmail ? 'email' : 'téléphone';
            
            // Envoie le code de vérification
            $success = $this->passwordService->requestPasswordReset($credential);

            if (!$success) {
                return $this->errorResponse(
                    'Erreur lors de l\'envoi du code de vérification', 
                    500
                );
            }

            return $this->successResponse(
                [
                    'credential_type' => $credentialType,
                    'message' => $isEmail 
                        ? 'Un code de réinitialisation a été envoyé à votre adresse email' 
                        : 'Un code de réinitialisation a été envoyé à votre numéro de téléphone',
                    'expires_in' => 15 // minutes
                ],
                'Code de vérification envoyé avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Une erreur est survenue lors de la demande de réinitialisation', 
                500
            );
        }
    }
}