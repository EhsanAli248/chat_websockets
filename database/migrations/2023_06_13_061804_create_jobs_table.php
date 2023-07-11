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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('title');
            $table->text('description');
            $table->enum('price_type', ['hourly', 'fixed'])->default('hourly');
            $table->decimal('min_price', 10, 4)->default(0);
            $table->decimal('max_price', 10, 4)->default(0);
            $table->string('duration', 100);
            $table->json('skills');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
