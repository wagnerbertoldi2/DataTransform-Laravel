<?php

namespace App\Services;

use App\Models\PrecoInsercao;
use App\Models\ProdutoInsercao;
use Illuminate\Support\Facades\DB;

class SincronizacaoService
{
    /**
     * Sincroniza produtos da view_produtos para produto_insercao.
     */
    public function sincronizarProdutos(): array
    {
        $produtosView = DB::table('view_produtos')->get();
        $codigosNaView = $produtosView->pluck('codigo')->toArray();

        $inseridos = 0;
        $atualizados = 0;

        foreach ($produtosView as $produto) {
            $resultado = ProdutoInsercao::updateOrCreate(
                ['codigo' => $produto->codigo],
                [
                    'nome' => $produto->nome,
                    'categoria' => $produto->categoria,
                    'subcategoria' => $produto->subcategoria,
                    'descricao' => $produto->descricao,
                    'fabricante' => $produto->fabricante,
                    'modelo' => $produto->modelo,
                    'cor' => $produto->cor,
                    'peso_kg' => $produto->peso_kg,
                    'largura_cm' => $produto->largura_cm,
                    'altura_cm' => $produto->altura_cm,
                    'profundidade_cm' => $produto->profundidade_cm,
                    'unidade' => $produto->unidade,
                    'data_cadastro' => $produto->data_cadastro,
                ]
            );

            if ($resultado->wasRecentlyCreated) {
                $inseridos++;
            } else {
                $atualizados++;
            }
        }

        // Remover produtos que não estão mais na view (inativos)
        $removidos = ProdutoInsercao::whereNotIn('codigo', $codigosNaView)->delete();

        return [
            'inseridos' => $inseridos,
            'atualizados' => $atualizados,
            'removidos' => $removidos,
        ];
    }

    /**
     * Sincroniza preços da view_precos para preco_insercao.
     */
    public function sincronizarPrecos(): array
    {
        $precosView = DB::table('view_precos')->get();
        $codigosNaView = $precosView->pluck('codigo_produto')->toArray();

        $inseridos = 0;
        $atualizados = 0;

        foreach ($precosView as $preco) {
            // Só sincroniza se o produto já foi sincronizado
            if (ProdutoInsercao::where('codigo', $preco->codigo_produto)->exists()) {
                $resultado = PrecoInsercao::updateOrCreate(
                    ['codigo_produto' => $preco->codigo_produto],
                    [
                        'valor' => $preco->valor,
                        'moeda' => $preco->moeda,
                        'desconto_percentual' => $preco->desconto_percentual,
                        'acrescimo_percentual' => $preco->acrescimo_percentual,
                        'valor_promocional' => $preco->valor_promocional,
                        'dt_inicio_promo' => $preco->dt_inicio_promo,
                        'dt_fim_promo' => $preco->dt_fim_promo,
                        'dt_atualizacao' => $preco->dt_atualizacao,
                        'origem' => $preco->origem,
                        'tipo_cliente' => $preco->tipo_cliente,
                        'vendedor_responsavel' => $preco->vendedor_responsavel,
                        'observacao' => $preco->observacao,
                        'status' => $preco->status,
                    ]
                );

                if ($resultado->wasRecentlyCreated) {
                    $inseridos++;
                } else {
                    $atualizados++;
                }
            }
        }

        // Remover preços cujo produto não está mais na view
        $removidos = PrecoInsercao::whereNotIn('codigo_produto', $codigosNaView)->delete();

        return [
            'inseridos' => $inseridos,
            'atualizados' => $atualizados,
            'removidos' => $removidos,
        ];
    }
}
