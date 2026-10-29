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
        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->text('quote_th')->nullable();
            $table->text('quote_en')->nullable();
            $table->text('quote_zh')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('testimonials')->insert([
            ['name' => 'Kitsada Chokevichitkul', 'quote_th' => '“ตอนแรกยังไม่แน่ใจว่าธุรกิจของเราเหมาะกับการติดตั้งโซลาร์เซลล์ไหม ทีม GSB Sun Power ช่วยอธิบายและประเมินจากการใช้ไฟจริง ทำให้เห็นภาพและตัดสินใจได้ง่ายขึ้นครับ”', 'rating' => 5, 'image_path' => 'image/review-01.jpg', 'sort_order' => 1, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Siwanath Simueang', 'quote_th' => '“ชอบที่ทีมเข้ามาดูทั้งพื้นที่หลังคาและรูปแบบการใช้ไฟของโรงงานก่อนเสนอระบบ พร้อมอธิบายเหตุผลในการเลือกขนาดติดตั้ง ทำให้เรามีข้อมูลประกอบการวางแผนลงทุนค่ะ”', 'rating' => 5, 'image_path' => 'image/review-02.jpg', 'sort_order' => 2, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MI Kornpong', 'quote_th' => '“โครงการมีรายละเอียดที่ต้องประสานงานหลายส่วน แต่ทีม GSB Sun Power แจ้งขั้นตอนและติดตามความคืบหน้าให้ตลอด เวลามีข้อสงสัยก็มีคนช่วยประสานงาน ทำให้ทำงานร่วมกันได้สะดวกครับ”', 'rating' => 5, 'image_path' => 'image/review-03.jpg', 'sort_order' => 3, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BOW Kanokwan', 'quote_th' => '“เรากังวลว่างานติดตั้งจะกระทบการทำงานประจำวัน ทีมจึงเข้ามาคุยและวางแผนร่วมกันก่อนเริ่มงาน มีการแจ้งกำหนดการให้เตรียมตัวล่วงหน้า ประทับใจความใส่ใจตรงนี้ค่ะ”', 'rating' => 5, 'image_path' => 'image/review-04.jpg', 'sort_order' => 4, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Jirayuth', 'quote_th' => '“หลังติดตั้ง ทีมช่วยสอนวิธีดูข้อมูลการผลิตไฟและอธิบายการใช้งานระบบให้เข้าใจ พอมีคำถามเพิ่มเติมก็สามารถติดต่อสอบถามได้ ทำให้เรามั่นใจในการใช้งานมากขึ้นครับ”', 'rating' => 5, 'image_path' => 'image/review-05.png', 'sort_order' => 5, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pinsuda Tangmo', 'quote_th' => '“เราต้องการนำพลังงานสะอาดมาใช้ในธุรกิจ แต่ยังไม่รู้ว่าจะเริ่มตรงไหน การได้คุยกับ GSB Sun Power ช่วยให้เห็นทั้งแนวทางติดตั้งและสิ่งที่ต้องเตรียม ทำให้แผนของเราชัดเจนขึ้นค่ะ”', 'rating' => 5, 'image_path' => 'image/review-06.png', 'sort_order' => 6, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
