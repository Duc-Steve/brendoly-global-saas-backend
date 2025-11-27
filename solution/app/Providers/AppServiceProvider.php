<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;     // importe Schema
use App\Core\Utils\ModuleLoader; // importe ModuleLoader

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application.
     */
    public function register(): void
    {
        // Ajoute ici les services à enregistrer si besoin
    }

    /**
     * Démarre les services de l'application.
     */
    public function boot(): void
    {
        // Fixe la longueur par défaut des chaînes pour éviter les erreurs MySQL
        Schema::defaultStringLength(191);

        // Charge tous les modules au démarrage
        ModuleLoader::loadModules();
    }
}
