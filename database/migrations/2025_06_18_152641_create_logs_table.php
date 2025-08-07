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
        Schema::create('logs', function (Blueprint $table) {
           $table->id();
           $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
           $table->string('action'); // ex: login, product_created, etc.
           $table->text('details')->nullable(); // message libre ou JSON
           $table->ipAddress('ip_address')->nullable(); // bonus
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
