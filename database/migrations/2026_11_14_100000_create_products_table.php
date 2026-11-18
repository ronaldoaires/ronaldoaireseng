<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ebooks and spreadsheets share one table: same sales page, same checkout,
        // same library. The "type" column decides the URL prefix and a few labels.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // ebook | spreadsheet (App\Enums\ProductType)
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->string('summary', 300);        // cards, checkout and Google fallback
            $table->longText('body')->nullable();  // sales copy (RichEditor HTML)
            $table->string('cover_path')->nullable();

            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable(); // "de R$ X por R$ Y"

            $table->json('highlights')->nullable(); // ["O que você vai aprender", ...]
            $table->json('specs')->nullable();      // [{label: "Páginas", value: "180"}, ...]
            $table->json('faq')->nullable();        // [{question, answer}, ...]

            $table->string('version', 30)->nullable();
            $table->boolean('has_reader')->default(false); // online (digital) version for buyers

            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 160)->nullable();

            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_published', 'sort_order']);
        });

        // Files the buyer downloads (PDF, xlsx, xlsm, zip). Always on the private disk.
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');                 // "Ebook em PDF", "Planilha (Windows)"
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('version', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Digital (online) version of an ebook, one row per chapter.
        Schema::create('product_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('body')->nullable();
            $table->boolean('is_preview')->default(false); // free sample on the sales page
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_chapters');
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('products');
    }
};
