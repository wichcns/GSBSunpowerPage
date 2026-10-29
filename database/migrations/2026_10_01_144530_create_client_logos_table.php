<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_logos', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->enum('rail', ['left', 'right'])->default('left')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $rows = [];
        foreach (['client-01.jpg', 'client-02.png', 'client-03.jpg', 'client-04.png', 'client-05.png', 'client-06.jpg', 'client-07.png', 'client-08.png', 'client-09.jpg', 'client-10.jpg', 'client-11.png', 'client-12.png'] as $index => $file) {
            $rows[] = ['name' => 'Client logo '.($index + 1), 'image_path' => 'image/client-logos/'.$file, 'alt_text' => 'GSB SunPower client logo', 'rail' => 'left', 'sort_order' => $index + 1, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach (['solar-client-01.jpg', 'solar-client-02.jpg', 'solar-client-03.jpg', 'solar-client-04.jpg', 'solar-client-05.jpg', 'solar-client-06.jpg', 'solar-client-07.png', 'solar-client-08.jpg', 'solar-client-09.jpg', 'solar-client-10.png', 'solar-client-11.jpg', 'solar-client-12.jpg'] as $index => $file) {
            $rows[] = ['name' => 'Solar client logo '.($index + 1), 'image_path' => 'image/solar-client-logos/'.$file, 'alt_text' => 'GSB SunPower solar-cell customer logo', 'rail' => 'right', 'sort_order' => $index + 1, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('client_logos')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_logos');
    }
};
