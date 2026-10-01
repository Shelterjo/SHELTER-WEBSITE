<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global search (docs/platform/GLOBAL-SEARCH.md):
     * - `search_index` is DERIVED, never a source: `php artisan search:rebuild` empties and rebuilds it from the
     *   published data (deterministic — the same rows every time). Both languages on every row; scope PUBLIC/OWNER
     *   is decided by the route on the server, never by the request.
     * - `search_query_daily` holds anonymous daily counters only (PRIV-011): no session, user, IP or user agent.
     *   Writing is off until PO-019 (feature flag `search.log`).
     */
    public function up(): void
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 20);
            $table->string('entity_id', 40);
            $table->string('scope', 10);
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('meta_ar')->nullable();
            $table->string('meta_en')->nullable();
            $table->string('normalized_title');
            $table->text('normalized_text');
            $table->string('url_ar')->nullable();
            $table->string('url_en')->nullable();
            $table->smallInteger('boost')->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamp('updated_at')->nullable();
            $table->unique(['scope', 'entity_type', 'entity_id']);
        });

        Schema::create('search_query_daily', function (Blueprint $table) {
            $table->id();
            $table->date('day');
            $table->string('scope', 10);
            $table->string('locale', 5);
            $table->string('query_norm', 50);
            $table->unsignedInteger('searches')->default(0);
            $table->unsignedInteger('zero_results')->default(0);
            $table->string('selected_type', 20)->nullable();
            $table->unsignedInteger('selected_count')->default(0);
            $table->unique(['day', 'scope', 'locale', 'query_norm']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_query_daily');
        Schema::dropIfExists('search_index');
    }
};
