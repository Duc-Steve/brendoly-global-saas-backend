<?php

namespace App\Core\Auth\ControllersATN;

use App\Http\Controllers\Controller;
use App\Core\Auth\RequestsATN\ProfileRequest;
use App\Core\Auth\ServicesATN\AuthService;
use App\Core\Auth\TraitsATN\ApiResponseTrait;
use App\Core\MultiTenancy\ServicesMTY\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * Controller pour la gestion du profil utilisateur
 * @package App\Core\Auth\Controllers
 */
class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService,
        private TenantService $tenantService
    ) {}

    /**
     * Récupère le profil complet de l'utilisateur
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $this->authService->getProfile($user);

            return $this->successResponse(
                $profile,
                'Profil récupéré avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération du profil', 
                500
            );
        }
    }

    /**
     * Met à jour le profil utilisateur
     */
    public function update(ProfileRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $request->user();
            
            // Met à jour uniquement les champs fournis
            $updateData = [];
            $updatableFields = ['first_name', 'last_name', 'email', 'phone'];
            
            foreach ($updatableFields as $field) {
                if (isset($validated[$field])) {
                    $updateData[$field] = $validated[$field];
                }
            }

            // Effectue la mise à jour si des champs sont modifiés
            if (!empty($updateData)) {
                $user->update($updateData);
                
                // Recharge l'utilisateur avec les relations
                $user->refresh();
            }

            // Récupère le profil mis à jour
            $profile = $this->authService->getProfile($user);

            return $this->successResponse(
                $profile,
                'Profil mis à jour avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour du profil', 
                500
            );
        }
    }

    /**
     * Met à jour les informations de l'entreprise (tenant)
     */
    public function updateCompany(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'type' => 'sometimes|string|max:100',
                'sector' => 'sometimes|string|max:100',
                'employees_number' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'zipcode' => 'nullable|string|max:20',
                'country' => 'sometimes|string|max:100',
            ]);

            $user = $request->user();
            
            if (!$user->tenant_id) {
                return $this->errorResponse(
                    'Aucune entreprise associée à votre compte', 
                    400
                );
            }

            $tenant = $user->tenant;
            $updateData = $request->only([
                'name', 'type', 'sector', 'employees_number', 
                'address', 'city', 'zipcode', 'country'
            ]);

            // Nettoie les données vides
            $updateData = array_filter($updateData, function ($value) {
                return $value !== null && $value !== '';
            });

            if (!empty($updateData)) {
                $tenant->update($updateData);
            }

            // Récupère les informations mises à jour
            $tenantInfo = $this->tenantService->getTenantInfo($user->tenant_id);

            return $this->successResponse(
                [
                    'tenant' => $tenantInfo
                ],
                'Informations de l\'entreprise mises à jour avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour des informations de l\'entreprise', 
                500
            );
        }
    }

    /**
     * Récupère les informations de l'entreprise
     */
    public function getCompany(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->tenant_id) {
                return $this->errorResponse(
                    'Aucune entreprise associée à votre compte', 
                    400
                );
            }

            $tenantInfo = $this->tenantService->getTenantInfo($user->tenant_id);

            if (!$tenantInfo) {
                return $this->errorResponse(
                    'Entreprise non trouvée', 
                    404
                );
            }

            return $this->successResponse(
                [
                    'tenant' => $tenantInfo
                ],
                'Informations de l\'entreprise récupérées avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des informations de l\'entreprise', 
                500
            );
        }
    }

    /**
     * Désactive le compte utilisateur
     */
    public function deactivateAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'confirmation' => 'required|string|in:YES,DÉSACTIVER',
            ]);

            $user = $request->user();

            if ($request->confirmation !== 'YES' && $request->confirmation !== 'DÉSACTIVER') {
                return $this->errorResponse(
                    'Confirmation invalide. Tapez "YES" ou "DÉSACTIVER" pour confirmer.', 
                    400
                );
            }

            // Désactive le compte
            $user->update([
                'is_active' => false
            ]);

            // Révoque tous les tokens
            $user->tokens()->delete();

            return $this->successResponse(
                null,
                'Votre compte a été désactivé avec succès'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la désactivation du compte', 
                500
            );
        }
    }
}