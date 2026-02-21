<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_insercao', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nome', 150);
            $table->string('categoria', 50)->nullable();
            $table->string('subcategoria', 50)->nullable();
            $table->text('descricao')->nullable();
            $table->string('fabricante', 100)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->string('cor', 30)->nullable();
            $table->decimal('peso_kg', 10, 3)->nullable();
            $table->decimal('largura_cm', 10, 2)->nullable();
            $table->decimal('altura_cm', 10, 2)->nullable();
            $table->decimal('profundidade_cm', 10, 2)->nullable();
            $table->string('unidade', 10)->nullable();
            $table->date('data_cadastro')->nullable();
            $table->timestamps();
        });

        Schema::create('preco_insercao', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_produto', 30);
            $table->decimal('valor', 10, 2);
            $table->string('moeda', 3)->default('BRL');
            $table->decimal('desconto_percentual', 5, 2)->nullable();
            $table->decimal('acrescimo_percentual', 5, 2)->nullable();
            $table->decimal('valor_promocional', 10, 2)->nullable();
            $table->date('dt_inicio_promo')->nullable();
            $table->date('dt_fim_promo')->nullable();
            $table->date('dt_atualizacao')->nullable();
            $table->string('origem', 50)->nullable();
            $table->string('tipo_cliente', 30)->nullable();
            $table->string('vendedor_responsavel', 100)->nullable();
            $table->text('observacao')->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();

            $table->index('codigo_produto');
            $table->foreign('codigo_produto')
                  ->references('codigo')
                  ->on('produto_insercao')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preco_insercao');
        Schema::dropIfExists('produto_insercao');
    }
};
