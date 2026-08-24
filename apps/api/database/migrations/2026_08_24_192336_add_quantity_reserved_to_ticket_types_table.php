<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table): void {
            $table->integer('quantity_reserved')->default(0);
        });

        DB::statement(
            'ALTER TABLE ticket_types
             ADD CONSTRAINT ticket_types_quantity_reserved_valid
             CHECK (
                quantity_reserved >= 0
                AND quantity_sold + quantity_reserved <= quantity
             )',
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE ticket_types
             DROP CONSTRAINT IF EXISTS ticket_types_quantity_reserved_valid',
        );

        Schema::table('ticket_types', function (Blueprint $table): void {
            $table->dropColumn('quantity_reserved');
        });
    }
};