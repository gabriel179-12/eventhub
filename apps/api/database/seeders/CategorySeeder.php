<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Educação', 'slug' => 'educacao'],
            ['name' => 'Esportes', 'slug' => 'esportes'],
            ['name' => 'Gastronomia', 'slug' => 'gastronomia'],
            ['name' => 'Música', 'slug' => 'musica'],
            ['name' => 'Negócios', 'slug' => 'negocios'],
            ['name' => 'Tecnologia', 'slug' => 'tecnologia'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']],
            );
        }
    }
}