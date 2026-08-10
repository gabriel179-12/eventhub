<?php

test('tests use the isolated database', function (): void {
    expect(config('database.connections.pgsql.database'))
        ->toBe('eventhub_test');
});