<?php

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the category seeder creates the canonical categories', function (): void {
    $this->seed(CategorySeeder::class);

    expect(Category::query()->orderBy('slug')->pluck('slug')->all())
        ->toBe([
            'educacao',
            'esportes',
            'gastronomia',
            'musica',
            'negocios',
            'tecnologia',
        ]);
});

test('the category seeder can be executed more than once', function (): void {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())
        ->toBe(6);
});

test('the database seeder includes the canonical categories', function (): void {
    $this->seed();

    expect(Category::query()->count())
        ->toBe(6);
});