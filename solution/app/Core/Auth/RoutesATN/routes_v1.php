<?php

use Illuminate\Support\Facades\Route;
use App\Core\Auth\ControllersATN\{
    AuthController,
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController,
    ProfileController
};

// Groupe de routes sous le préfixe "auth"
Route::prefix('auth')->group(function () {
    
    // === ROUTES PUBLIQUES ===
    
    // Inscription
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Connexion
    Route::post('/login', [LoginController::class, 'login']);
    
    // Mot de passe oublié
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode']);
    
    // Réinitialisation du mot de passe (public)
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
    

    // === ROUTES PROTÉGÉES ===
    Route::middleware(['auth.api'])->group(function () {
        
        // Gestion du profil
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'getProfile']);
            Route::put('/update', [ProfileController::class, 'update']);
            Route::get('/company', [ProfileController::class, 'getCompany']);
            Route::put('/company', [ProfileController::class, 'updateCompany']);
            Route::post('/deactivate', [ProfileController::class, 'deactivateAccount']);
        });
        
    });
});