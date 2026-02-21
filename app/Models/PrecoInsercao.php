<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecoInsercao extends Model
{
    protected $table = 'preco_insercao';

    protected $fillable = [
        'codigo_produto', 'valor', 'moeda', 'desconto_percentual',
        'acrescimo_percentual', 'valor_promocional', 'dt_inicio_promo',
        'dt_fim_promo', 'dt_atualizacao', 'origem', 'tipo_cliente',
        'vendedor_responsavel', 'observacao', 'status',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'acrescimo_percentual' => 'decimal:2',
        'valor_promocional' => 'decimal:2',
        'dt_inicio_promo' => 'date',
        'dt_fim_promo' => 'date',
        'dt_atualizacao' => 'date',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(ProdutoInsercao::class, 'codigo_produto', 'codigo');
    }
}
