<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('event_id')
                ->constrained()
                ->restrictOnDelete();

            $table->uuid('code')->unique();
            $table->string('status', 20)->default('pending');
            $table->integer('total_in_cents')->default(0);

            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index(['status', 'expires_at']);
        });

        DB::statement(
            "ALTER TABLE orders
             ADD CONSTRAINT orders_status_valid
             CHECK (status IN ('pending', 'paid', 'cancelled', 'expired'))"
        );

        DB::statement(
            'ALTER TABLE orders
             ADD CONSTRAINT orders_total_in_cents_non_negative
             CHECK (total_in_cents >= 0)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};