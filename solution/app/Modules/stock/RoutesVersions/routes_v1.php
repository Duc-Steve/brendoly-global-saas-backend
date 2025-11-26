<?php

use Illuminate\Support\Facades\Route;
use App\Module\Stock\Controllers\StockController;

// Définition des routes pour le module Stock
// Ces routes sont accessibles uniquement si l'utilisateur est authentifié et si le middleware 'tenant' est validé
Route::middleware(['auth:sanctum', 'tenant'])->prefix('stock')->group(function () {

    // Route GET pour récupérer la liste des stocks
    Route::get('/', [StockController::class, 'index']);  

    // Route POST pour créer un nouveau stock
    Route::post('/create', [StockController::class, 'store']);  

    // Route PUT pour mettre à jour un stock existant par son ID
    Route::put('/{id}', [StockController::class, 'update']);  

    // Route DELETE pour supprimer un stock par son ID
    Route::delete('/{id}', [StockController::class, 'destroy']);  
});
