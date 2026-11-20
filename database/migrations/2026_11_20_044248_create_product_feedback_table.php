<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Only reviews fill this column; MySQL permits multiple nulls for suggestions.
            $table->foreignId('review_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('pending');
            $table->string('display_name', 100);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('subject', 120)->nullable();
            $table->text('content');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'review_user_id']);
            $table->index(['product_id', 'type', 'status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_feedback');
    }
};
