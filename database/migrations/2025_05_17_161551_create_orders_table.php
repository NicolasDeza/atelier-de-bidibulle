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
        Schema::create('orders', function (Blueprint $table) {
             $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_method_id')->constrained()->onDelete('cascade');
            $table->dateTime('ordered_at');

            $table->enum('status', ['pending', 'confirmed', 'shipped', 'completed', 'cancelled'])->index();
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered', 'returned'])->index();
            $table->string('tracking_number')->nullable();

            $table->enum('payment_status', ['unpaid', 'paid', 'pending_refund'])->default('unpaid')->index();
            $table->string('payment_method')->index(); //  stripe, paypal, bancontact

            $table->decimal('total_price', 8, 2);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
