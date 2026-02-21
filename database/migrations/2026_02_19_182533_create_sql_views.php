<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // View de Produtos — Normalização completa dos dados
        DB::statement("
            CREATE VIEW view_produtos AS
            SELECT 
                UPPER(TRIM(prod_cod)) as codigo,
                TRIM(
                    REPLACE(
                        REPLACE(
                            REPLACE(prod_nome, '   ', ' '),
                        '  ', ' '),
                    '  ', ' ')
                ) as nome,
                UPPER(TRIM(prod_cat)) as categoria,
                UPPER(TRIM(prod_subcat)) as subcategoria,
                TRIM(prod_desc) as descricao,
                TRIM(prod_fab) as fabricante,
                TRIM(prod_mod) as modelo,
                TRIM(prod_cor) as cor,
                CAST(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    LOWER(TRIM(prod_peso)),
                                'kg', ''),
                            'g', ''),
                        ',', '.'),
                    ' ', '')
                AS REAL) / 
                CASE 
                    WHEN LOWER(TRIM(prod_peso)) LIKE '%kg%' THEN 1
                    WHEN LOWER(TRIM(prod_peso)) LIKE '%g%' THEN 1000
                    ELSE 1 
                END as peso_kg,
                CAST(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                LOWER(TRIM(prod_larg)),
                            'cm', ''),
                        ',', '.'),
                    ' ', '')
                AS REAL) as largura_cm,
                CAST(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                LOWER(TRIM(prod_alt)),
                            'cm', ''),
                        ',', '.'),
                    ' ', '')
                AS REAL) as altura_cm,
                CAST(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                LOWER(TRIM(prod_prof)),
                            'cm', ''),
                        ',', '.'),
                    ' ', '')
                AS REAL) as profundidade_cm,
                UPPER(TRIM(prod_und)) as unidade,
                CASE
                    WHEN prod_dt_cad LIKE '%/%/%' AND LENGTH(prod_dt_cad) = 10 AND SUBSTR(prod_dt_cad,3,1) = '/' 
                        THEN SUBSTR(prod_dt_cad,7,4) || '-' || SUBSTR(prod_dt_cad,4,2) || '-' || SUBSTR(prod_dt_cad,1,2)
                    WHEN prod_dt_cad LIKE '%/%/%' 
                        THEN REPLACE(prod_dt_cad, '/', '-')
                    WHEN prod_dt_cad LIKE '%.%.%' 
                        THEN REPLACE(prod_dt_cad, '.', '-')
                    WHEN prod_dt_cad LIKE '__-__-____' 
                        THEN SUBSTR(prod_dt_cad,7,4) || '-' || SUBSTR(prod_dt_cad,4,2) || '-' || SUBSTR(prod_dt_cad,1,2)
                    ELSE prod_dt_cad
                END as data_cadastro
            FROM produtos_base
            WHERE prod_atv = 1
        ");

        // View de Preços — Normalização completa dos dados
        // Lógica de parsing de valores monetários:
        //   - Se contém vírgula: formato BR (1.099,00 → remove '.', troca ',' por '.')
        //   - Se não contém vírgula: formato US/inteiro (120.50 ou 899.99 → manter como está)
        DB::statement("
            CREATE VIEW view_precos AS
            SELECT 
                UPPER(TRIM(prc_cod_prod)) as codigo_produto,
                CASE 
                    WHEN TRIM(prc_valor) = 'sem preço' OR TRIM(prc_valor) = '0' OR TRIM(prc_valor) = '' THEN NULL
                    WHEN INSTR(TRIM(prc_valor), ',') > 0 THEN
                        CAST(REPLACE(REPLACE(TRIM(prc_valor), '.', ''), ',', '.') AS REAL)
                    ELSE
                        CAST(REPLACE(TRIM(prc_valor), ' ', '') AS REAL)
                END as valor,
                UPPER(TRIM(prc_moeda)) as moeda,
                CASE
                    WHEN prc_desc IS NULL OR TRIM(prc_desc) = '' THEN NULL
                    ELSE CAST(REPLACE(REPLACE(TRIM(prc_desc), '%', ''), ',', '.') AS REAL)
                END as desconto_percentual,
                CASE
                    WHEN prc_acres IS NULL OR TRIM(prc_acres) = '0' OR TRIM(prc_acres) = '' THEN NULL
                    ELSE CAST(REPLACE(REPLACE(TRIM(prc_acres), '%', ''), ',', '.') AS REAL)
                END as acrescimo_percentual,
                CASE
                    WHEN prc_promo IS NULL OR TRIM(prc_promo) = '' THEN NULL
                    WHEN INSTR(TRIM(prc_promo), ',') > 0 THEN
                        CAST(REPLACE(REPLACE(TRIM(prc_promo), '.', ''), ',', '.') AS REAL)
                    ELSE
                        CAST(REPLACE(TRIM(prc_promo), ' ', '') AS REAL)
                END as valor_promocional,
                CASE
                    WHEN prc_dt_ini_promo IS NULL THEN NULL
                    WHEN prc_dt_ini_promo LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_ini_promo)) = 10 AND SUBSTR(TRIM(prc_dt_ini_promo),3,1) = '/' 
                        THEN SUBSTR(TRIM(prc_dt_ini_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),1,2)
                    WHEN prc_dt_ini_promo LIKE '%/%/%' 
                        THEN REPLACE(TRIM(prc_dt_ini_promo), '/', '-')
                    WHEN prc_dt_ini_promo LIKE '%.%.%' 
                        THEN REPLACE(TRIM(prc_dt_ini_promo), '.', '-')
                    WHEN TRIM(prc_dt_ini_promo) LIKE '__-__-____' 
                        THEN SUBSTR(TRIM(prc_dt_ini_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),1,2)
                    ELSE TRIM(prc_dt_ini_promo)
                END as dt_inicio_promo,
                CASE
                    WHEN prc_dt_fim_promo IS NULL THEN NULL
                    WHEN prc_dt_fim_promo LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_fim_promo)) = 10 AND SUBSTR(TRIM(prc_dt_fim_promo),3,1) = '/' 
                        THEN SUBSTR(TRIM(prc_dt_fim_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),1,2)
                    WHEN prc_dt_fim_promo LIKE '%/%/%' 
                        THEN REPLACE(TRIM(prc_dt_fim_promo), '/', '-')
                    WHEN prc_dt_fim_promo LIKE '%.%.%' 
                        THEN REPLACE(TRIM(prc_dt_fim_promo), '.', '-')
                    WHEN TRIM(prc_dt_fim_promo) LIKE '__-__-____' 
                        THEN SUBSTR(TRIM(prc_dt_fim_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),1,2)
                    ELSE TRIM(prc_dt_fim_promo)
                END as dt_fim_promo,
                CASE
                    WHEN prc_dt_atual IS NULL THEN NULL
                    WHEN prc_dt_atual LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_atual)) = 10 AND SUBSTR(TRIM(prc_dt_atual),3,1) = '/' 
                        THEN SUBSTR(TRIM(prc_dt_atual),7,4) || '-' || SUBSTR(TRIM(prc_dt_atual),4,2) || '-' || SUBSTR(TRIM(prc_dt_atual),1,2)
                    WHEN prc_dt_atual LIKE '%/%/%' 
                        THEN REPLACE(TRIM(prc_dt_atual), '/', '-')
                    WHEN prc_dt_atual LIKE '%.%.%' 
                        THEN REPLACE(TRIM(prc_dt_atual), '.', '-')
                    WHEN TRIM(prc_dt_atual) LIKE '__-__-____' 
                        THEN SUBSTR(TRIM(prc_dt_atual),7,4) || '-' || SUBSTR(TRIM(prc_dt_atual),4,2) || '-' || SUBSTR(TRIM(prc_dt_atual),1,2)
                    ELSE TRIM(prc_dt_atual)
                END as dt_atualizacao,
                UPPER(TRIM(prc_origem)) as origem,
                UPPER(TRIM(prc_tipo_cli)) as tipo_cliente,
                TRIM(prc_vend_resp) as vendedor_responsavel,
                TRIM(prc_obs) as observacao,
                LOWER(TRIM(prc_status)) as status
            FROM precos_base
            WHERE LOWER(TRIM(prc_status)) = 'ativo'
              AND TRIM(prc_valor) != 'sem preço'
              AND TRIM(prc_valor) != '0'
              AND TRIM(prc_valor) != ''
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_precos");
        DB::statement("DROP VIEW IF EXISTS view_produtos");
    }
};
