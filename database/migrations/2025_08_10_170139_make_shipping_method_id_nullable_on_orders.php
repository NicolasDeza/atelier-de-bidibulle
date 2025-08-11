<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // rendre nullable
        DB::statement('ALTER TABLE `orders` MODIFY `shipping_method_id` BIGINT UNSIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // remettre NOT NULL
        DB::statement('ALTER TABLE `orders` MODIFY `shipping_method_id` BIGINT UNSIGNED NOT NULL;');
    }
};
