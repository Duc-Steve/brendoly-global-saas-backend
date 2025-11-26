<?php

use Illuminate\Support\Facades\Route;

// Middleware commun appliqué à toutes les routes API
// $commonMiddleware = ['api'];

// --- Routes du core Authentication ---
// Charge les routes d'authentification de l'application
require base_path('app/Core/Auth/RoutesATN/routes_v1.php');

// --- Routes des modules ---
// Parcourt chaque module présent dans app/Modules et charge son fichier routes.php si existant
foreach (glob(base_path('app/Modules/*/RoutesVersions/routes_v1.php')) as $moduleRoutes) {
    require $moduleRoutes;
}
