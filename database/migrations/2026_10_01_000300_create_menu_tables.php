<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menu master data (M33 §4–§5, D-086, D-092, D-133–D-136, docs/phase-01-discovery/17-menu-data-model-draft.md).
     * - One product identity (PRD-#####, frozen, never reused) for every channel; no per-channel product copies.
     * - Source rows are immutable lineage (SRC-#####); corrections live in normalized/display columns only.
     * - Prices are integer fils, VAT inclusive, versioned by validity window; branch overrides are explicit rows.
     */
    public function up(): void
    {
        Schema::create('menu_versions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('status', 20);
            $table->date('effective_from');
            $table->string('source_file')->nullable();
            $table->string('source_hash', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->foreignId('menu_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_name');
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_ar_status', 60);
            $table->string('suggested_name_ar')->nullable();
            $table->string('slug', 60)->nullable()->unique();
            $table->string('type', 20)->default('standard');
            $table->date('season_starts_on')->nullable();
            $table->date('season_ends_on')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('menu_subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('menu_category_id')->constrained()->restrictOnDelete();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('status', 30);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->foreignId('menu_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('menu_subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->foreignId('merged_into_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('normalized_name_en')->nullable();
            $table->string('normalized_name_ar')->nullable();
            $table->string('display_name_en')->nullable();
            $table->string('display_name_ar')->nullable();
            $table->string('name_en_status', 80);
            $table->string('name_en_decision', 80)->nullable();
            $table->string('name_ar_status', 80);
            $table->string('name_ar_decision', 80)->nullable();
            $table->string('suggested_name_ar')->nullable();
            $table->string('availability', 20)->default('available');
            $table->string('publish_status', 20)->default('draft');
            $table->string('size_info_status', 60)->nullable();
            $table->boolean('show_addons')->default(false);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_seasonal')->default(false);
            $table->string('data_quality_status', 60)->nullable();
            $table->string('data_quality_flags', 300)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['menu_category_id', 'sort']);
        });

        Schema::create('menu_source_rows', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('menu_version_id')->constrained()->restrictOnDelete();
            $table->string('source_sheet', 60);
            $table->unsignedInteger('source_row');
            $table->unsignedInteger('source_sequence_number');
            $table->string('source_lineage', 191);
            $table->string('source_category_name');
            $table->string('source_name_en');
            $table->string('source_name_ar')->nullable();
            $table->string('source_price', 20);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_version_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('price_fils');
            $table->string('currency', 3);
            $table->boolean('tax_inclusive')->default(true);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->string('source', 30);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'valid_from']);
        });

        Schema::create('product_branch_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price_fils')->nullable();
            $table->string('availability', 20)->nullable();
            $table->string('reason', 500)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_branch_overrides');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('menu_source_rows');
        Schema::dropIfExists('products');
        Schema::dropIfExists('menu_subcategories');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('menu_groups');
        Schema::dropIfExists('menu_versions');
    }
};
