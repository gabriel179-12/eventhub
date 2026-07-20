<?php

test('health check responds successfully', function (): void {
    $this->getJson('/api/v1/health')
        ->assertOK()
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.service', 'eventhub-api');
});
