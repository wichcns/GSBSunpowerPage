<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('category', 40)->default('solar');
            $table->string('title_th', 220)->nullable();
            $table->string('title_en', 220)->nullable();
            $table->string('title_zh', 220)->nullable();
            $table->text('excerpt_th')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_zh')->nullable();
            $table->longText('content_th')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_zh')->nullable();
            $table->string('cover_image')->nullable();
            $table->date('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('blog_videos', function (Blueprint $table): void {
            $table->id();
            $table->string('title_th', 220)->nullable();
            $table->string('title_en', 220)->nullable();
            $table->string('title_zh', 220)->nullable();
            $table->string('video_url', 1000);
            $table->string('thumbnail_image')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_videos');
        Schema::dropIfExists('blog_posts');
    }
};
