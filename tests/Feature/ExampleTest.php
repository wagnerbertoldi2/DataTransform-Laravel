<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que a API está respondendo.
     */
    public function test_api_produtos_precos_retorna_sucesso(): void
    {
        $response = $this->getJson('/api/produtos-precos');

        $response->assertStatus(200);
    }
}
