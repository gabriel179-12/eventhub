<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('order_item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->uuid('code')->unique();
            $table->string('status', 20)->default('valid');

            $table->timestampTz('checked_in_at')->nullable();

            $table->foreignId('checked_in_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('order_item_id');
            $table->index(['status', 'checked_in_at']);
        });

        DB::statement(
            "ALTER TABLE tickets
             ADD CONSTRAINT tickets_status_valid
             CHECK (status IN ('valid', 'used', 'cancelled'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};