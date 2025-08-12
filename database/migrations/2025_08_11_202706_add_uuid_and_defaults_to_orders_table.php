<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Ajouter la colonne uuid (nullable d'abord)
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // 1b) Renseigner un uuid pour chaque ligne existante
        DB::table('orders')->whereNull('uuid')->orWhere('uuid', '')->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('orders')->where('id', $row->id)->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
                }
            });

        // 1c) Rendre uuid NOT NULL + unique
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });

        // 2) Définir les valeurs par défaut (sans doctrine/dbal)
        DB::statement("ALTER TABLE `orders` MODIFY `payment_provider` varchar(50) NULL DEFAULT 'stripe'");
        DB::statement("ALTER TABLE `orders` MODIFY `currency` char(3) NOT NULL DEFAULT 'EUR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Revenir aux defaults précédents
        DB::statement("ALTER TABLE `orders` MODIFY `payment_provider` varchar(50) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY `currency` char(3) NOT NULL");

        // Supprimer uuid + index
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
