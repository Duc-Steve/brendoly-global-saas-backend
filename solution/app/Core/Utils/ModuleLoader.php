<?php

namespace App\Core\Utils;

class ModuleLoader
{
    /**
     * Charge les modules activés dans le fichier de configuration.
     */
    public static function loadModules()
    {
        // Récupère tous les modules de la configuration
        $allModules = config('modules.modules', []);
        
        // Filtre seulement les modules activés
        $modulesFromConfig = [];
        foreach ($allModules as $moduleName => $moduleConfig) {
            if ($moduleConfig['enabled'] ?? false) {
                $modulesFromConfig[] = $moduleConfig['slug'] ?? strtolower($moduleName);
            }
        }

        // Si aucun module dans la nouvelle structure, essaye l'ancienne
        if (empty($modulesFromConfig)) {
            $modulesFromConfig = config('modules.enabled', []);
        }

        // Si null, convertir en tableau vide
        if ($modulesFromConfig === null) {
            $modulesFromConfig = [];
        }

        // Chemin vers les modules placés dans app/Module
        $modulesPath = base_path('app/Module');

        // Liste finale des modules actifs
        $enabledModules = [];

        /*
         * 1. Charge les modules déclarés dans config/modules.php
         */
        foreach ($modulesFromConfig as $module) {
            // Chemin vers module.json
            $jsonPath = base_path("modules/$module/modules.json");

            // Vérifie la présence du fichier
            if (file_exists($jsonPath)) {
                // Ajoute le module à la liste des modules actifs
                $enabledModules[] = $module;

                // Charge les routes si un fichier existe
                $routesFile = base_path("modules/$module/routes.php");
                if (file_exists($routesFile)) {
                    require $routesFile;
                }
            }
        }

        /*
         * 2. Charge les modules trouvés dans app/Module
         */
        if (is_dir($modulesPath)) {
            foreach (glob($modulesPath . '/*/modules.json') as $file) {
                // Lis module.json
                $module = json_decode(file_get_contents($file), true);

                // Charge uniquement les modules activés
                if ($module['enabled'] ?? false) {
                    // Ajoute l'identifiant du module
                    $enabledModules[] = $module['slug'] ?? $module['name'];

                    // Charge les routes si présentes
                    $routesFile = dirname($file) . '/routes.php';
                    if (file_exists($routesFile)) {
                        require $routesFile;
                    }
                }
            }
        }

        /*
         * 3. Écrit les modules actifs dans un fichier cache
         */
        $runtimePath = base_path('runtime');
        if (!is_dir($runtimePath)) {
            mkdir($runtimePath, 0755, true);
        }
        
        file_put_contents(
            base_path('runtime/modules-cache.json'),
            json_encode(array_unique($enabledModules))
        );
    }
}