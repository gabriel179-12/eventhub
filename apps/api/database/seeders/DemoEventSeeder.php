<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoEventSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $organizer = Organizer::query()->firstOrCreate(
                ['slug' => 'eventhub-demo'],
                ['name' => 'EventHub Demonstração'],
            );

            $category = Category::query()->firstOrCreate(
                ['slug' => 'tecnologia'],
                ['name' => 'Tecnologia'],
            );

            $startsAt = now()->addDays(14)->setTime(19, 0);

            $event = Event::query()->firstOrNew([
                'slug' => 'encontro-eventhub-demo',
            ]);

            $event->fill([
                'category_id' => $category->id,
                'name' => 'Encontro de Tecnologia EventHub',
                'description' => 'Um encontro para aprender e compartilhar experiências sobre tecnologia.',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHours(3),
                'venue_name' => 'Centro de Convenções',
                'address_line' => 'Rua Principal, 100',
                'city' => 'São Paulo',
                'state' => 'SP',
                'postal_code' => '01001-000',
                'capacity' => 500,
                'is_private' => false,
            ]);

            $event->organizer()->associate($organizer);
            $event->status = 'published';
            $event->save();
        });
    }
}