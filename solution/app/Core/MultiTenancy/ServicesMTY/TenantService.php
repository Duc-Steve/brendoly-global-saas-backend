<?php

namespace App\Core\MultiTenancy\ServicesMTY;

use App\Core\MultiTenancy\ModelsMTY\Tenant;
use Exception;

/**
 * Service de gestion des tenants
 * Permet de récupérer les informations d'un tenant par son ID
 */
class TenantService
{
    /**
     * Récupère les informations d'un tenant
     * 
     * @param string $tenantId ID du tenant
     * @return array|null Retourne un tableau d'infos ou null si le tenant n'existe pas
     */
    public function getTenantInfo(string $tenantId): ?array
    {
        // Recherche du tenant dans la base
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return null; // Aucun tenant trouvé
        }

        // Retourne un tableau structuré avec les informations du tenant
        return [
            'id' => $tenant->id_tenant,
            'name' => $tenant->name,               // Décrypté automatiquement
            'type' => $tenant->type,               // Décrypté automatiquement
            'sector' => $tenant->sector,           // Décrypté automatiquement
            'employees_number' => $tenant->employees_number, // Décrypté automatiquement
            'address' => $tenant->address,         // Décrypté automatiquement
            'city' => $tenant->city,               // Décrypté automatiquement
            'zipcode' => $tenant->zipcode,
            'country' => $tenant->country,         // Décrypté automatiquement
            'is_active' => $tenant->is_active,
        ];
    }
}
