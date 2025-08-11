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
            // Ajout email invité (si pas de user)
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('user_id');
            }

            // Champs Stripe (sans webhook)
            if (!Schema::hasColumn('orders', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->char('currency', 3)->default('EUR')->after('payment_intent_id');
            }

            // Frais de livraison séparés + montant reçu
            if (!Schema::hasColumn('orders', 'shipping_amount')) {
                $table->decimal('shipping_amount', 8, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('orders', 'amount_received')) {
                $table->decimal('amount_received', 8, 2)->nullable()->after('shipping_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // On vérifie avant de supprimer (sécurisé)
            if (Schema::hasColumn('orders', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
            if (Schema::hasColumn('orders', 'payment_intent_id')) {
                $table->dropColumn('payment_intent_id');
            }
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('orders', 'shipping_amount')) {
                $table->dropColumn('shipping_amount');
            }
            if (Schema::hasColumn('orders', 'amount_received')) {
                $table->dropColumn('amount_received');
            }
        });
    }
};
