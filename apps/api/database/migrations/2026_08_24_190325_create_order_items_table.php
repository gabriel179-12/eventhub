<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('ticket_type_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('ticket_type_name', 100);
            $table->integer('unit_price_in_cents');
            $table->integer('quantity');
            $table->integer('total_in_cents');

            $table->timestamps();

            $table->unique(['order_id', 'ticket_type_id']);
            $table->index('ticket_type_id');
        });

        DB::statement(
            'ALTER TABLE order_items
             ADD CONSTRAINT order_items_unit_price_non_negative
             CHECK (unit_price_in_cents >= 0)'
        );

        DB::statement(
            'ALTER TABLE order_items
             ADD CONSTRAINT order_items_quantity_positive
             CHECK (quantity > 0)'
        );

        DB::statement(
            'ALTER TABLE order_items
             ADD CONSTRAINT order_items_total_matches_unit_price
             CHECK (total_in_cents = unit_price_in_cents * quantity)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};