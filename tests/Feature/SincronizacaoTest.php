<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SincronizacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar views para o ambiente de teste
        try {
            DB::statement("
                CREATE VIEW IF NOT EXISTS view_produtos AS
                SELECT 
                    UPPER(TRIM(prod_cod)) as codigo,
                    TRIM(REPLACE(REPLACE(REPLACE(prod_nome, '   ', ' '), '  ', ' '), '  ', ' ')) as nome,
                    UPPER(TRIM(prod_cat)) as categoria,
                    UPPER(TRIM(prod_subcat)) as subcategoria,
                    TRIM(prod_desc) as descricao,
                    TRIM(prod_fab) as fabricante,
                    TRIM(prod_mod) as modelo,
                    TRIM(prod_cor) as cor,
                    CAST(
                        REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(prod_peso)), 'kg', ''), 'g', ''), ',', '.'), ' ', '')
                    AS REAL) / 
                    CASE WHEN LOWER(TRIM(prod_peso)) LIKE '%kg%' THEN 1 WHEN LOWER(TRIM(prod_peso)) LIKE '%g%' THEN 1000 ELSE 1 END as peso_kg,
                    CAST(REPLACE(REPLACE(REPLACE(LOWER(TRIM(prod_larg)), 'cm', ''), ',', '.'), ' ', '') AS REAL) as largura_cm,
                    CAST(REPLACE(REPLACE(REPLACE(LOWER(TRIM(prod_alt)), 'cm', ''), ',', '.'), ' ', '') AS REAL) as altura_cm,
                    CAST(REPLACE(REPLACE(REPLACE(LOWER(TRIM(prod_prof)), 'cm', ''), ',', '.'), ' ', '') AS REAL) as profundidade_cm,
                    UPPER(TRIM(prod_und)) as unidade,
                    CASE
                        WHEN prod_dt_cad LIKE '%/%/%' AND LENGTH(prod_dt_cad) = 10 AND SUBSTR(prod_dt_cad,3,1) = '/' 
                            THEN SUBSTR(prod_dt_cad,7,4) || '-' || SUBSTR(prod_dt_cad,4,2) || '-' || SUBSTR(prod_dt_cad,1,2)
                        WHEN prod_dt_cad LIKE '%/%/%' THEN REPLACE(prod_dt_cad, '/', '-')
                        WHEN prod_dt_cad LIKE '%.%.%' THEN REPLACE(prod_dt_cad, '.', '-')
                        WHEN prod_dt_cad LIKE '__-__-____' 
                            THEN SUBSTR(prod_dt_cad,7,4) || '-' || SUBSTR(prod_dt_cad,4,2) || '-' || SUBSTR(prod_dt_cad,1,2)
                        ELSE prod_dt_cad
                    END as data_cadastro
                FROM produtos_base
                WHERE prod_atv = 1
            ");

            DB::statement("
                CREATE VIEW IF NOT EXISTS view_precos AS
                SELECT 
                    UPPER(TRIM(prc_cod_prod)) as codigo_produto,
                    CASE 
                        WHEN TRIM(prc_valor) = 'sem preço' OR TRIM(prc_valor) = '0' OR TRIM(prc_valor) = '' THEN NULL
                        ELSE CAST(REPLACE(REPLACE(REPLACE(TRIM(prc_valor), '.', ''), ',', '.'), ' ', '') AS REAL)
                    END as valor,
                    UPPER(TRIM(prc_moeda)) as moeda,
                    CASE WHEN prc_desc IS NULL OR TRIM(prc_desc) = '' THEN NULL
                        ELSE CAST(REPLACE(REPLACE(TRIM(prc_desc), '%', ''), ',', '.') AS REAL) END as desconto_percentual,
                    CASE WHEN prc_acres IS NULL OR TRIM(prc_acres) = '0' OR TRIM(prc_acres) = '' THEN NULL
                        ELSE CAST(REPLACE(REPLACE(TRIM(prc_acres), '%', ''), ',', '.') AS REAL) END as acrescimo_percentual,
                    CASE WHEN prc_promo IS NULL OR TRIM(prc_promo) = '' THEN NULL
                        ELSE CAST(REPLACE(REPLACE(REPLACE(TRIM(prc_promo), '.', ''), ',', '.'), ' ', '') AS REAL) END as valor_promocional,
                    CASE
                        WHEN prc_dt_ini_promo IS NULL THEN NULL
                        WHEN prc_dt_ini_promo LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_ini_promo)) = 10 AND SUBSTR(TRIM(prc_dt_ini_promo),3,1) = '/' 
                            THEN SUBSTR(TRIM(prc_dt_ini_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),1,2)
                        WHEN prc_dt_ini_promo LIKE '%/%/%' THEN REPLACE(TRIM(prc_dt_ini_promo), '/', '-')
                        WHEN prc_dt_ini_promo LIKE '%.%.%' THEN REPLACE(TRIM(prc_dt_ini_promo), '.', '-')
                        WHEN TRIM(prc_dt_ini_promo) LIKE '__-__-____' 
                            THEN SUBSTR(TRIM(prc_dt_ini_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_ini_promo),1,2)
                        ELSE TRIM(prc_dt_ini_promo)
                    END as dt_inicio_promo,
                    CASE
                        WHEN prc_dt_fim_promo IS NULL THEN NULL
                        WHEN prc_dt_fim_promo LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_fim_promo)) = 10 AND SUBSTR(TRIM(prc_dt_fim_promo),3,1) = '/' 
                            THEN SUBSTR(TRIM(prc_dt_fim_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),1,2)
                        WHEN prc_dt_fim_promo LIKE '%/%/%' THEN REPLACE(TRIM(prc_dt_fim_promo), '/', '-')
                        WHEN prc_dt_fim_promo LIKE '%.%.%' THEN REPLACE(TRIM(prc_dt_fim_promo), '.', '-')
                        WHEN TRIM(prc_dt_fim_promo) LIKE '__-__-____' 
                            THEN SUBSTR(TRIM(prc_dt_fim_promo),7,4) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),4,2) || '-' || SUBSTR(TRIM(prc_dt_fim_promo),1,2)
                        ELSE TRIM(prc_dt_fim_promo)
                    END as dt_fim_promo,
                    CASE
                        WHEN prc_dt_atual IS NULL THEN NULL
                        WHEN prc_dt_atual LIKE '%/%/%' AND LENGTH(TRIM(prc_dt_atual)) = 10 AND SUBSTR(TRIM(prc_dt_atual),3,1) = '/' 
                            THEN SUBSTR(TRIM(prc_dt_atual),7,4) || '-' || SUBSTR(TRIM(prc_dt_atual),4,2) || '-' || SUBSTR(TRIM(prc_dt_atual),1,2)
                        WHEN prc_dt_atual LIKE '%/%/%' THEN REPLACE(TRIM(prc_dt_atual), '/', '-')
                        WHEN prc_dt_atual LIKE '%.%.%' THEN REPLACE(TRIM(prc_dt_atual), '.', '-')
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
        } catch (\Exception $e) {
            // Views já existem
        }
    }

    public function test_sincronizacao_produtos_insere_apenas_ativos(): void
    {
        // Inserir produto ativo e inativo
        DB::table('produtos_base')->insert([
            ['prod_cod' => 'PRD001', 'prod_nome' => 'Teclado RGB', 'prod_cat' => 'PERIFERICOS', 'prod_subcat' => 'TECLADOS', 'prod_desc' => 'Teclado mecânico', 'prod_fab' => 'HyperTech', 'prod_mod' => 'HT-1', 'prod_cor' => 'Preto', 'prod_peso' => '1.2kg', 'prod_larg' => '45cm', 'prod_alt' => '5cm', 'prod_prof' => '15cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '2025-10-10'],
            ['prod_cod' => 'PRD002', 'prod_nome' => 'Mouse Gamer', 'prod_cat' => 'PERIFERICOS', 'prod_subcat' => 'MOUSES', 'prod_desc' => 'Mouse óptico', 'prod_fab' => 'TechPro', 'prod_mod' => 'TP-1', 'prod_cor' => 'Preto', 'prod_peso' => '95g', 'prod_larg' => '6cm', 'prod_alt' => '3cm', 'prod_prof' => '10cm', 'prod_und' => 'UN', 'prod_atv' => false, 'prod_dt_cad' => '2025-10-10'],
        ]);

        $response = $this->postJson('/api/sincronizar/produtos');

        $response->assertStatus(200)
            ->assertJsonFragment(['inseridos' => 1]);

        $this->assertDatabaseHas('produto_insercao', ['codigo' => 'PRD001']);
        $this->assertDatabaseMissing('produto_insercao', ['codigo' => 'PRD002']);
    }

    public function test_sincronizacao_produtos_normaliza_dados(): void
    {
        DB::table('produtos_base')->insert([
            'prod_cod' => ' prd001 ', 'prod_nome' => '  Teclado  Mecânico  ', 'prod_cat' => 'perifericos',
            'prod_subcat' => 'teclados', 'prod_desc' => 'Descrição', 'prod_fab' => ' HyperTech ',
            'prod_mod' => 'HT-1', 'prod_cor' => ' Preto ', 'prod_peso' => '1,2kg ', 'prod_larg' => '45 CM',
            'prod_alt' => '5cm', 'prod_prof' => '15cm', 'prod_und' => ' un ', 'prod_atv' => true,
            'prod_dt_cad' => '2025/10/10',
        ]);

        $this->postJson('/api/sincronizar/produtos');

        $this->assertDatabaseHas('produto_insercao', [
            'codigo' => 'PRD001',
            'categoria' => 'PERIFERICOS',
            'subcategoria' => 'TECLADOS',
            'unidade' => 'UN',
        ]);
    }

    public function test_sincronizacao_precos_sucesso(): void
    {
        // Criar produto base e sincronizar
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'PERIFERICOS',
            'prod_subcat' => 'TECLADOS', 'prod_desc' => 'Teste', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);
        $this->postJson('/api/sincronizar/produtos');

        // Criar preço base
        DB::table('precos_base')->insert([
            'prc_cod_prod' => 'PRD001', 'prc_valor' => '499,90', 'prc_moeda' => 'brl',
            'prc_desc' => '5%', 'prc_acres' => '0', 'prc_promo' => '474,90',
            'prc_dt_ini_promo' => '2025-10-10', 'prc_dt_fim_promo' => '2025-10-20',
            'prc_dt_atual' => '2025-10-15', 'prc_origem' => 'ERP', 'prc_tipo_cli' => 'VAREJO',
            'prc_vend_resp' => 'Marcos', 'prc_obs' => 'Teste', 'prc_status' => 'ativo',
        ]);

        $response = $this->postJson('/api/sincronizar/precos');

        $response->assertStatus(200)
            ->assertJsonFragment(['inseridos' => 1]);

        $this->assertDatabaseHas('preco_insercao', [
            'codigo_produto' => 'PRD001',
            'moeda' => 'BRL',
        ]);
    }

    public function test_precos_inativos_nao_sincronizam(): void
    {
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'PERIFERICOS',
            'prod_subcat' => 'TECLADOS', 'prod_desc' => 'Teste', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);
        $this->postJson('/api/sincronizar/produtos');

        DB::table('precos_base')->insert([
            'prc_cod_prod' => 'PRD001', 'prc_valor' => 'sem preço', 'prc_moeda' => 'BRL',
            'prc_desc' => null, 'prc_acres' => null, 'prc_promo' => null,
            'prc_dt_ini_promo' => null, 'prc_dt_fim_promo' => null,
            'prc_dt_atual' => '2025-10-10', 'prc_origem' => 'ERP', 'prc_tipo_cli' => 'VAREJO',
            'prc_vend_resp' => 'Pedro', 'prc_obs' => 'Sem preço', 'prc_status' => 'inativo',
        ]);

        $response = $this->postJson('/api/sincronizar/precos');

        $response->assertStatus(200)
            ->assertJsonFragment(['inseridos' => 0]);

        $this->assertDatabaseMissing('preco_insercao', ['codigo_produto' => 'PRD001']);
    }

    public function test_listagem_produtos_paginada(): void
    {
        // Criar e sincronizar múltiplos produtos
        for ($i = 1; $i <= 3; $i++) {
            DB::table('produtos_base')->insert([
                'prod_cod' => "PRD00{$i}", 'prod_nome' => "Produto {$i}", 'prod_cat' => 'CAT',
                'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
                'prod_mod' => "M{$i}", 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
                'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
                'prod_dt_cad' => '2025-01-01',
            ]);
        }
        $this->postJson('/api/sincronizar/produtos');

        $response = $this->getJson('/api/produtos-precos?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['total' => 3]);
    }

    public function test_sincronizacao_remove_produtos_inativos(): void
    {
        // Inserir produto ativo e sincronizar
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'CAT',
            'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);
        $this->postJson('/api/sincronizar/produtos');
        $this->assertDatabaseHas('produto_insercao', ['codigo' => 'PRD001']);

        // Desativar o produto e ressincronizar
        DB::table('produtos_base')->where('prod_cod', 'PRD001')->update(['prod_atv' => false]);
        $response = $this->postJson('/api/sincronizar/produtos');

        $response->assertStatus(200)
            ->assertJsonFragment(['removidos' => 1]);

        $this->assertDatabaseMissing('produto_insercao', ['codigo' => 'PRD001']);
    }

    public function test_sincronizacao_atualiza_produto_existente(): void
    {
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado V1', 'prod_cat' => 'CAT',
            'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);

        // Primeira sincronização
        $this->postJson('/api/sincronizar/produtos');
        $this->assertDatabaseHas('produto_insercao', ['nome' => 'Teclado V1']);

        // Atualizar nome
        DB::table('produtos_base')->where('prod_cod', 'PRD001')->update(['prod_nome' => 'Teclado V2']);

        // Segunda sincronização
        $response = $this->postJson('/api/sincronizar/produtos');
        $response->assertJsonFragment(['atualizados' => 1]);

        $this->assertDatabaseHas('produto_insercao', ['nome' => 'Teclado V2']);
        $this->assertDatabaseMissing('produto_insercao', ['nome' => 'Teclado V1']);
    }

    public function test_conversao_peso_gramas_para_quilogramas(): void
    {
        DB::table('produtos_base')->insert([
            ['prod_cod' => 'PRD001', 'prod_nome' => 'Produto A', 'prod_cat' => 'CAT', 'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab', 'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '500g', 'prod_larg' => '10cm', 'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '2025-01-01'],
            ['prod_cod' => 'PRD002', 'prod_nome' => 'Produto B', 'prod_cat' => 'CAT', 'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab', 'prod_mod' => 'M2', 'prod_cor' => 'Preto', 'prod_peso' => '2,5kg', 'prod_larg' => '10cm', 'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '2025-01-01'],
        ]);

        $this->postJson('/api/sincronizar/produtos');

        $produtoA = DB::table('produto_insercao')->where('codigo', 'PRD001')->first();
        $produtoB = DB::table('produto_insercao')->where('codigo', 'PRD002')->first();

        // 500g = 0.5kg
        $this->assertEquals(0.5, (float) $produtoA->peso_kg);
        // 2,5kg = 2.5kg
        $this->assertEquals(2.5, (float) $produtoB->peso_kg);
    }

    public function test_normalizacao_formatos_de_data(): void
    {
        DB::table('produtos_base')->insert([
            ['prod_cod' => 'PRD001', 'prod_nome' => 'Prod 1', 'prod_cat' => 'CAT', 'prod_subcat' => 'SUB', 'prod_desc' => 'D', 'prod_fab' => 'F', 'prod_mod' => 'M', 'prod_cor' => 'P', 'prod_peso' => '1kg', 'prod_larg' => '1cm', 'prod_alt' => '1cm', 'prod_prof' => '1cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '10/10/2025'],
            ['prod_cod' => 'PRD002', 'prod_nome' => 'Prod 2', 'prod_cat' => 'CAT', 'prod_subcat' => 'SUB', 'prod_desc' => 'D', 'prod_fab' => 'F', 'prod_mod' => 'M', 'prod_cor' => 'P', 'prod_peso' => '1kg', 'prod_larg' => '1cm', 'prod_alt' => '1cm', 'prod_prof' => '1cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '2025-10-10'],
            ['prod_cod' => 'PRD003', 'prod_nome' => 'Prod 3', 'prod_cat' => 'CAT', 'prod_subcat' => 'SUB', 'prod_desc' => 'D', 'prod_fab' => 'F', 'prod_mod' => 'M', 'prod_cor' => 'P', 'prod_peso' => '1kg', 'prod_larg' => '1cm', 'prod_alt' => '1cm', 'prod_prof' => '1cm', 'prod_und' => 'UN', 'prod_atv' => true, 'prod_dt_cad' => '2025.10.10'],
        ]);

        $this->postJson('/api/sincronizar/produtos');

        // Todos devem resultar no formato YYYY-MM-DD
        $datas = DB::table('produto_insercao')->pluck('data_cadastro', 'codigo');

        $this->assertStringStartsWith('2025-10-10', $datas['PRD001']);
        $this->assertStringStartsWith('2025-10-10', $datas['PRD002']);
        $this->assertStringStartsWith('2025-10-10', $datas['PRD003']);
    }

    public function test_sincronizacao_idempotente_nao_duplica(): void
    {
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'CAT',
            'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);

        // Executar 3 vezes seguidas
        $this->postJson('/api/sincronizar/produtos');
        $this->postJson('/api/sincronizar/produtos');
        $response = $this->postJson('/api/sincronizar/produtos');

        $response->assertStatus(200);

        // Deve existir apenas 1 registro, sem duplicidades
        $total = DB::table('produto_insercao')->where('codigo', 'PRD001')->count();
        $this->assertEquals(1, $total);
    }

    public function test_preco_sem_produto_correspondente_nao_sincroniza(): void
    {
        // Inserir preço sem ter produto sincronizado
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'CAT',
            'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);

        // NÃO sincronizar produtos — apenas inserir preço
        DB::table('precos_base')->insert([
            'prc_cod_prod' => 'PRD001', 'prc_valor' => '100,00', 'prc_moeda' => 'BRL',
            'prc_desc' => null, 'prc_acres' => null, 'prc_promo' => null,
            'prc_dt_ini_promo' => null, 'prc_dt_fim_promo' => null,
            'prc_dt_atual' => '2025-01-01', 'prc_origem' => 'ERP', 'prc_tipo_cli' => 'VAREJO',
            'prc_vend_resp' => 'Ana', 'prc_obs' => null, 'prc_status' => 'ativo',
        ]);

        $response = $this->postJson('/api/sincronizar/precos');

        $response->assertStatus(200);

        // Preço não deve ser inserido pois produto não foi sincronizado
        $this->assertDatabaseMissing('preco_insercao', ['codigo_produto' => 'PRD001']);
    }

    public function test_sincronizacao_atualiza_preco_existente(): void
    {
        // Criar e sincronizar produto
        DB::table('produtos_base')->insert([
            'prod_cod' => 'PRD001', 'prod_nome' => 'Teclado', 'prod_cat' => 'CAT',
            'prod_subcat' => 'SUB', 'prod_desc' => 'Desc', 'prod_fab' => 'Fab',
            'prod_mod' => 'M1', 'prod_cor' => 'Preto', 'prod_peso' => '1kg', 'prod_larg' => '10cm',
            'prod_alt' => '5cm', 'prod_prof' => '2cm', 'prod_und' => 'UN', 'prod_atv' => true,
            'prod_dt_cad' => '2025-01-01',
        ]);
        $this->postJson('/api/sincronizar/produtos');

        // Criar e sincronizar preço
        DB::table('precos_base')->insert([
            'prc_cod_prod' => 'PRD001', 'prc_valor' => '100,00', 'prc_moeda' => 'BRL',
            'prc_desc' => null, 'prc_acres' => null, 'prc_promo' => null,
            'prc_dt_ini_promo' => null, 'prc_dt_fim_promo' => null,
            'prc_dt_atual' => '2025-01-01', 'prc_origem' => 'ERP', 'prc_tipo_cli' => 'VAREJO',
            'prc_vend_resp' => 'Ana', 'prc_obs' => null, 'prc_status' => 'ativo',
        ]);
        $this->postJson('/api/sincronizar/precos');

        // Alterar valor do preço
        DB::table('precos_base')->where('prc_cod_prod', 'PRD001')->update(['prc_valor' => '200,00']);

        // Ressincronizar
        $response = $this->postJson('/api/sincronizar/precos');
        $response->assertJsonFragment(['atualizados' => 1]);

        // Deve ter o valor atualizado
        $preco = DB::table('preco_insercao')->where('codigo_produto', 'PRD001')->first();
        $this->assertEquals(200.0, (float) $preco->valor);
    }
}
