<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SincronizacaoService;
use Illuminate\Http\JsonResponse;

class SincronizacaoController extends Controller
{
    protected SincronizacaoService $service;

    public function __construct(SincronizacaoService $service)
    {
        $this->service = $service;
    }

    public function sincronizarProdutos(): JsonResponse
    {
        $resultado = $this->service->sincronizarProdutos();

        return response()->json([
            'mensagem' => 'Sincronização de produtos concluída.',
            'inseridos' => $resultado['inseridos'],
            'atualizados' => $resultado['atualizados'],
            'removidos' => $resultado['removidos'],
        ]);
    }

    public function sincronizarPrecos(): JsonResponse
    {
        $resultado = $this->service->sincronizarPrecos();

        return response()->json([
            'mensagem' => 'Sincronização de preços concluída.',
            'inseridos' => $resultado['inseridos'],
            'atualizados' => $resultado['atualizados'],
            'removidos' => $resultado['removidos'],
        ]);
    }
}
