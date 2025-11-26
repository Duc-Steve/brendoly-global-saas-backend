<?php

use App\Core\Auth\Middleware\AuthMiddleware;
use App\Core\Auth\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;

// Exemple d’enregistrement middleware global pour toutes les versions
Route::middleware([AuthMiddleware::class, TenantMiddleware::class])->group(function () {
    // Routes qui nécessitent auth + tenant
});