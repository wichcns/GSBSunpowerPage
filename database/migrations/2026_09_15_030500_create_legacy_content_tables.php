<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 10)->index();
            $table->string('legacy_key')->unique();
            $table->string('title_en')->nullable();
            $table->string('title_th')->nullable();
            $table->string('title_zh')->nullable();
            $table->text('scale_en')->nullable();
            $table->text('scale_th')->nullable();
            $table->text('scale_zh')->nullable();
            $table->text('configuration_en')->nullable();
            $table->text('configuration_th')->nullable();
            $table->text('configuration_zh')->nullable();
            $table->text('location_en')->nullable();
            $table->text('location_th')->nullable();
            $table->text('location_zh')->nullable();
            $table->text('model_en')->nullable();
            $table->text('model_th')->nullable();
            $table->text('model_zh')->nullable();
            $table->text('overview_en')->nullable();
            $table->text('overview_th')->nullable();
            $table->text('overview_zh')->nullable();
            $table->string('status_th')->nullable();
            $table->string('status_zh')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('careers', function (Blueprint $table): void {
            $table->id();
            $table->string('legacy_key')->unique();
            $table->string('title_th');
            $table->string('title_zh');
            $table->text('location_th')->nullable();
            $table->text('location_zh')->nullable();
            $table->text('salary_th')->nullable();
            $table->text('salary_zh')->nullable();
            $table->text('description_th')->nullable();
            $table->text('description_zh')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('careers');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('projects');
    }
};
