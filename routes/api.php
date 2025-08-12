<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BairroController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas para Bairros
Route::prefix('bairros')->group(function () {
    // Rotas básicas CRUD
    Route::get('/', [BairroController::class, 'index']);
    Route::post('/', [BairroController::class, 'store']);
    Route::get('/{id}', [BairroController::class, 'show']);
    Route::patch('/{id}', [BairroController::class, 'update']);
    Route::delete('/{id}', [BairroController::class, 'destroy']);
    
    // Rotas especiais
    Route::get('/proximos', [BairroController::class, 'proximos']);
    Route::patch('/{id}/restore', [BairroController::class, 'restore']);
    Route::get('/estatisticas', [BairroController::class, 'estatisticas']);
    Route::get('/com-contagem-usuarios', [BairroController::class, 'comContagemUsuarios']);
    Route::get('/por-regiao', [BairroController::class, 'porRegiao']);
});
