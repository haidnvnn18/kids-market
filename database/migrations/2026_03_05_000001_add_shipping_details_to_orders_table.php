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
        Schema::table('orders', function (Blueprint $table) {
            // Lưu snapshot thông tin địa chỉ giao hàng tại thời điểm order
            $table->string('shipping_full_name')->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->text('shipping_address_detail')->nullable();
            $table->foreignId('shipping_province_id')->nullable()->constrained('provinces');
            $table->foreignId('shipping_district_id')->nullable()->constrained('districts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_full_name',
                'shipping_phone',
                'shipping_address_detail',
                'shipping_province_id',
                'shipping_district_id',
            ]);
        });
    }
};
