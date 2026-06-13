<?php

namespace Tests\Feature;

use Tests\TestCase;

class StatusApiTest extends TestCase
{
    public function test_status_endpoint_returns_server_health_payload(): void
    {
        $response = $this->getJson('/api/status');

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'framework' => 'Laravel',
                'message' => 'Servidor web funcionando',
            ]);
    }
}
