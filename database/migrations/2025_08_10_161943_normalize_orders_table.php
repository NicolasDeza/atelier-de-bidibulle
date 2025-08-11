<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Renommer vers nos noms finaux
            if (Schema::hasColumn('orders', 'payment_intent_id') && !Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->renameColumn('payment_intent_id', 'stripe_payment_intent_id');
            }
            if (Schema::hasColumn('orders', 'shipping_amount') && !Schema::hasColumn('orders', 'shipping_total')) {
                $table->renameColumn('shipping_amount', 'shipping_total');
            }

            // Ajouter paid_at si manquant
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });

        // Nettoyage (optionnel) : supprimer amount_received si tu n’en veux pas
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'amount_received')) {
                $table->dropColumn('amount_received');
            }
        });

        // Petit index utile pour retrouver une commande invitée
        Schema::table('orders', function (Blueprint $table) {
            // index non unique, safe à re-lancer
            $table->index('customer_email', 'orders_customer_email_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // rollback best-effort
            if (Schema::hasColumn('orders', 'stripe_payment_intent_id') && !Schema::hasColumn('orders', 'payment_intent_id')) {
                $table->renameColumn('stripe_payment_intent_id', 'payment_intent_id');
            }
            if (Schema::hasColumn('orders', 'shipping_total') && !Schema::hasColumn('orders', 'shipping_amount')) {
                $table->renameColumn('shipping_total', 'shipping_amount');
            }
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (!Schema::hasColumn('orders', 'amount_received')) {
                $table->decimal('amount_received', 8, 2)->nullable();
            }
            // l’index sera supprimé automatiquement si tu droppes la colonne, sinon on peut le laisser
        });
    }
};

