<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('event_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->integer('price_in_cents');
            $table->integer('quantity');
            $table->integer('quantity_sold')->default(0);

            $table->timestampTz('sales_starts_at')->nullable();
            $table->timestampTz('sales_ends_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['event_id', 'name']);
            $table->index(['event_id', 'is_active']);
        });

        DB::statement(
            'ALTER TABLE ticket_types
             ADD CONSTRAINT ticket_types_price_in_cents_non_negative
             CHECK (price_in_cents >= 0)'
        );

        DB::statement(
            'ALTER TABLE ticket_types
             ADD CONSTRAINT ticket_types_quantity_positive
             CHECK (quantity > 0)'
        );

        DB::statement(
            'ALTER TABLE ticket_types
             ADD CONSTRAINT ticket_types_quantity_sold_valid
             CHECK (quantity_sold >= 0 AND quantity_sold <= quantity)'
        );

        DB::statement(
            'ALTER TABLE ticket_types
             ADD CONSTRAINT ticket_types_sales_period_valid
             CHECK (
                sales_starts_at IS NULL
                OR sales_ends_at IS NULL
                OR sales_ends_at > sales_starts_at
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};