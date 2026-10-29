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
        Schema::create('solutions', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_th')->nullable();
            $table->string('title_zh')->nullable();
            $table->text('summary_en')->nullable();
            $table->text('summary_th')->nullable();
            $table->text('summary_zh')->nullable();
            $table->text('body_en')->nullable();
            $table->text('body_th')->nullable();
            $table->text('body_zh')->nullable();
            $table->string('image_path')->nullable();
            $table->string('destination_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solutions');
    }
};
