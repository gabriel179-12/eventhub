<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a visitor can list public categories ordered by name', function (): void {
    Category::query()->create([
        'name' => 'Tecnologia',
        'slug' => 'tecnologia',
    ]);
    Category::query()->create([
        'name' => 'Educação',
        'slug' => 'educacao',
    ]);
    Category::query()->create([
        'name' => 'Esportes',
        'slug' => 'esportes',
    ]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.slug', 'educacao')
        ->assertJsonPath('data.1.slug', 'esportes')
        ->assertJsonPath('data.2.slug', 'tecnologia');
});

test('a visitor can access an empty category list', function (): void {
    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});