<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BairroController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PontoInteresseController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\PostagemController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\GamificacaoController;
use App\Http\Controllers\NotificacaoController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota de teste
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando!',
        'timestamp' => now()
    ]);
});

// Rota de teste para eventos
Route::get('/test-eventos', function () {
    $eventos = \App\Models\Evento::count();
    return response()->json(['message' => 'EventoController funcionando!', 'total_eventos' => $eventos]);
});

// Rotas de autenticação (públicas)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-id', [AuthController::class, 'getUserId']);
});

// Rotas para Bairros (públicas)
Route::prefix('bairros')->group(function () {
    Route::get('/', [BairroController::class, 'index']);
    Route::post('/', [BairroController::class, 'store']);
    Route::get('/{id}', [BairroController::class, 'show']);
    Route::patch('/{id}', [BairroController::class, 'update']);
    Route::delete('/{id}', [BairroController::class, 'destroy']);
    Route::patch('/{id}/restore', [BairroController::class, 'restore']);
    Route::get('/{id}/proximos', [BairroController::class, 'proximos']);
    Route::get('/{id}/estatisticas', [BairroController::class, 'estatisticas']);
});

// Rotas para Usuários (públicas)
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::get('/estatisticas', [UsuarioController::class, 'estatisticas']);
    Route::get('/proximos', [UsuarioController::class, 'proximos']);
    Route::get('/{id}', [UsuarioController::class, 'show']);
    Route::patch('/{id}', [UsuarioController::class, 'update']);
    Route::delete('/{id}', [UsuarioController::class, 'destroy']);
    Route::patch('/{id}/restore', [UsuarioController::class, 'restore']);
});

// Rotas para Pontos de Interesse (públicas)
Route::prefix('pontos-interesse')->group(function () {
    Route::get('/', [PontoInteresseController::class, 'index']);
    Route::post('/', [PontoInteresseController::class, 'store']);
    Route::get('/proximos', [PontoInteresseController::class, 'proximos']);
    Route::get('/categorias', [PontoInteresseController::class, 'categorias']);
    Route::get('/estatisticas', [PontoInteresseController::class, 'estatisticas']);
    Route::get('/{id}', [PontoInteresseController::class, 'show']);
    Route::patch('/{id}', [PontoInteresseController::class, 'update']);
    Route::delete('/{id}', [PontoInteresseController::class, 'destroy']);
});

// Rotas para Eventos (públicas)
Route::prefix('eventos')->group(function () {
    Route::get('/', [EventoController::class, 'index']);
    Route::post('/', [EventoController::class, 'store']);
    Route::get('/{id}', [EventoController::class, 'show']);
    Route::patch('/{id}', [EventoController::class, 'update']);
    Route::delete('/{id}', [EventoController::class, 'destroy']);
});

// Rotas para Anúncios (públicas)
Route::prefix('anuncios')->group(function () {
    Route::get('/', [AnuncioController::class, 'index']);
    Route::post('/', [AnuncioController::class, 'store']);
    Route::get('/proximos', [AnuncioController::class, 'proximos']);
    Route::get('/categorias', [AnuncioController::class, 'categorias']);
    Route::get('/estatisticas', [AnuncioController::class, 'estatisticas']);
    Route::get('/{id}', [AnuncioController::class, 'show']);
    Route::patch('/{id}', [AnuncioController::class, 'update']);
    Route::delete('/{id}', [AnuncioController::class, 'destroy']);
});

// Rotas para Postagens (públicas)
Route::prefix('postagens')->group(function () {
    Route::get('/', [PostagemController::class, 'index']);
    Route::post('/', [PostagemController::class, 'store']);
    Route::get('/proximas', [PostagemController::class, 'proximas']);
    Route::get('/estatisticas', [PostagemController::class, 'estatisticas']);
    Route::get('/{id}', [PostagemController::class, 'show']);
    Route::patch('/{id}', [PostagemController::class, 'update']);
    Route::delete('/{id}', [PostagemController::class, 'destroy']);
});

// Rotas para Serviços (públicas)
Route::prefix('servicos')->group(function () {
    Route::get('/', [ServicoController::class, 'index']);
    Route::post('/', [ServicoController::class, 'store']);
    Route::get('/proximos', [ServicoController::class, 'proximos']);
    Route::get('/categorias', [ServicoController::class, 'categorias']);
    Route::get('/estatisticas', [ServicoController::class, 'estatisticas']);
    Route::get('/{id}', [ServicoController::class, 'show']);
    Route::patch('/{id}', [ServicoController::class, 'update']);
    Route::delete('/{id}', [ServicoController::class, 'destroy']);
});

// Rotas para Gamificação (públicas)
Route::prefix('gamificacao')->group(function () {
    Route::get('/', [GamificacaoController::class, 'index']);
    Route::post('/', [GamificacaoController::class, 'store']);
    Route::get('/estatisticas', [GamificacaoController::class, 'estatisticas']);
    Route::get('/{id}', [GamificacaoController::class, 'show']);
    Route::patch('/{id}', [GamificacaoController::class, 'update']);
    Route::delete('/{id}', [GamificacaoController::class, 'destroy']);
    Route::post('/{id}/adicionar-xp', [GamificacaoController::class, 'adicionarXP']);
    Route::post('/{id}/adicionar-gocoins', [GamificacaoController::class, 'adicionarGoCoins']);
});

// Rotas para Notificações (públicas)
Route::prefix('notificacoes')->group(function () {
    Route::get('/', [NotificacaoController::class, 'index']);
    Route::post('/', [NotificacaoController::class, 'store']);
    Route::patch('/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasComoLidas']);
    Route::get('/nao-lidas', [NotificacaoController::class, 'naoLidas']);
    Route::get('/estatisticas', [NotificacaoController::class, 'estatisticas']);
    Route::get('/{id}', [NotificacaoController::class, 'show']);
    Route::patch('/{id}', [NotificacaoController::class, 'update']);
    Route::delete('/{id}', [NotificacaoController::class, 'destroy']);
    Route::patch('/{id}/marcar-lida', [NotificacaoController::class, 'marcarComoLida']);
});
