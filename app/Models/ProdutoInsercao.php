<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoInsercao extends Model
{
    protected $table = 'produto_insercao';

    protected $fillable = [
        'codigo', 'nome', 'categoria', 'subcategoria', 'descricao',
        'fabricante', 'modelo', 'cor', 'peso_kg', 'largura_cm',
        'altura_cm', 'profundidade_cm', 'unidade', 'data_cadastro',
    ];

    protected $casts = [
        'peso_kg' => 'decimal:3',
        'largura_cm' => 'decimal:2',
        'altura_cm' => 'decimal:2',
        'profundidade_cm' => 'decimal:2',
        'data_cadastro' => 'date',
    ];

    public function precos(): HasMany
    {
        return $this->hasMany(PrecoInsercao::class, 'codigo_produto', 'codigo');
    }
}
