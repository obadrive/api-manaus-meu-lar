<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Bem-vindo à API do Portal Cidadão Manaus',
        'data' => [
            'version' => '1.0.0',
            'description' => 'API RESTful para o super app Portal Cidadão Manaus',
            'endpoints' => [
                'auth' => '/api/auth',
                'bairros' => '/api/bairros',
                'eventos' => '/api/eventos',
                'postagens' => '/api/postagens',
                'anuncios' => '/api/anuncios',
                'servicos' => '/api/servicos',
                'gamificacao' => '/api/gamificacao',
                'notificacoes' => '/api/notificacoes'
            ],
            'documentation' => 'Em desenvolvimento',
            'support' => 'suporte@portalmanaus.com.br'
        ]
    ]);
});
