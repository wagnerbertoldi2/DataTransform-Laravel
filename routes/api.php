<?php

use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\SincronizacaoController;
use Illuminate\Support\Facades\Route;

Route::prefix('sincronizar')->group(function () {
    Route::post('produtos', [SincronizacaoController::class, 'sincronizarProdutos']);
    Route::post('precos', [SincronizacaoController::class, 'sincronizarPrecos']);
});

Route::get('produtos-precos', [ProdutoController::class, 'index']);
