<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoBase extends Model
{
    public $timestamps = false;

    protected $table = 'produtos_base';

    protected $primaryKey = 'prod_id';

    protected $fillable = [
        'prod_cod', 'prod_nome', 'prod_cat', 'prod_subcat', 'prod_desc',
        'prod_fab', 'prod_mod', 'prod_cor', 'prod_peso', 'prod_larg',
        'prod_alt', 'prod_prof', 'prod_und', 'prod_atv', 'prod_dt_cad',
    ];

    protected $casts = [
        'prod_atv' => 'boolean',
    ];
}
