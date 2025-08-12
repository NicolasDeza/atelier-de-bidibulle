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
         Schema::table('orders', function (Blueprint $table) {
            // 2) Ajouts de colonnes
            $table->string('stripe_checkout_session_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('payment_provider', 50)->nullable()->after('payment_method');
        });

        // 1) Élargir l'ENUM payment_status
        // Ancien: ('unpaid','paid','pending_refund')
        // Nouveau: ('unpaid','processing','paid','failed','pending_refund','refunded')
        DB::statement("
            ALTER TABLE `orders`
            MODIFY `payment_status`
            ENUM('unpaid','processing','paid','failed','pending_refund','refunded')
            NOT NULL DEFAULT 'unpaid'
        ");;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Revenir à l'ENUM d'origine
        DB::statement("
            ALTER TABLE `orders`
            MODIFY `payment_status`
            ENUM('unpaid','paid','pending_refund')
            NOT NULL DEFAULT 'unpaid'
        ");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stripe_checkout_session_id');
            $table->dropColumn('payment_provider');
        });
    }
};
