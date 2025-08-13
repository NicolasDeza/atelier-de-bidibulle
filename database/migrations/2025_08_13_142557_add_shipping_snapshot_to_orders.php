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
            $table->string('shipping_method_label')->nullable()->after('shipping_total');
            $table->text('shipping_address_json')->nullable()->after('shipping_method_label');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_method_label', 'shipping_address_json']);
        });
    }
};
