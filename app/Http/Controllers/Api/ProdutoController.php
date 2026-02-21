<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProdutoInsercao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);

        $produtos = ProdutoInsercao::with('precos')
            ->paginate((int) $perPage);

        return response()->json($produtos);
    }
}
