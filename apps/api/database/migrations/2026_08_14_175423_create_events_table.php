<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('events', function(Blueprint $table): void{
        $table->id();

        $table->foreignId('organizer_id')
            ->constrained()
            ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description');

            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at')->nullable();

            $table->string('venue_name', 150);
            $table->string('address_line');
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('postal_code', 9);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->integer('capacity')->nullable();
            $table->string('banner_path')->nullable();

            $table->boolean('is_private')->default(false);
            $table->string('status', 20)->default('draft');

            $table->timestamps();

            $table->index(['organizer_id', 'status']);
            $table->index('starts_at');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
