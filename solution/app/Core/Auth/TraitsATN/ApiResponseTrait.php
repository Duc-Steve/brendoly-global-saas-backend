<?php

namespace App\Core\Auth\TraitsATN;

use Illuminate\Http\JsonResponse;

/**
 * Trait pour les réponses API standardisées
 * Permet de centraliser la structure des réponses JSON pour les API
 * @package App\Core\Auth\Traits
 */
trait ApiResponseTrait
{
    /**
     * Réponse de succès standardisée
     * @param mixed $data Les données à retourner
     * @param string|null $message Message optionnel
     * @param int $code Code HTTP (200 par défaut)
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Réponse d'erreur standardisée
     * @param string $message Message d'erreur
     * @param int $code Code HTTP (400 par défaut)
     * @param mixed|null $errors Détails supplémentaires sur l'erreur
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Réponse pour ressource créée
     * Utilise un code HTTP 201
     * @param mixed $data Données créées
     * @param string $message Message de succès
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse pour ressource non trouvée
     * Utilise un code HTTP 404
     * @param string $message Message d'erreur
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Réponse pour accès non autorisé
     * Utilise un code HTTP 401
     * @param string $message Message d'erreur
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }
}
