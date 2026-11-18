<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, failed, cancelled, refunded
            $table->string('source', 20)->default('checkout'); // checkout | manual (granted in the admin)
            $table->string('payment_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();

            // "Continue reading" in the library.
            $table->foreignId('last_chapter_id')->nullable()->constrained('product_chapters')->nullOnDelete();
            $table->timestamp('last_read_at')->nullable();

            $table->timestamps();

            // One row per user and product: a new attempt reuses the old row.
            $table->unique(['user_id', 'product_id']);
            $table->index(['status', 'paid_at']);
        });

        Schema::create('product_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_file_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('downloaded_at');

            $table->index(['user_id', 'downloaded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_downloads');
        Schema::dropIfExists('purchases');
    }
};
