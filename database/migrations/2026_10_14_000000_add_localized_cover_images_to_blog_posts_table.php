<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            if (!Schema::hasColumn('blog_posts', 'cover_image_th')) {
                $table->string('cover_image_th')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'cover_image_en')) {
                $table->string('cover_image_en')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'cover_image_zh')) {
                $table->string('cover_image_zh')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('blog_posts', 'cover_image_th') ? 'cover_image_th' : null,
                Schema::hasColumn('blog_posts', 'cover_image_en') ? 'cover_image_en' : null,
                Schema::hasColumn('blog_posts', 'cover_image_zh') ? 'cover_image_zh' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
