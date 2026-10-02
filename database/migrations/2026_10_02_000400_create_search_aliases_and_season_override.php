<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - `search_aliases` (CMS-018, SRCH-006/007, Menu IA §8 / §19 `search_alias`): other words customers may type for a
     *   menu item, in Arabic or English. Only the Owner adds them (adding = approving — F-15: never invented); they are
     *   archived, never deleted. Matching uses `normalized` (the same Normalizer as every search).
     * - `menu_categories.season_override` (CMS-009, MENU-044, F-17): null = the season follows its dates; `on` = shown
     *   whatever the dates; `off` = hidden whatever the dates.
     */
    public function up(): void
    {
        Schema::create('search_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('value', 60);
            $table->string('normalized', 60)->index();
            $table->string('locale', 2);
            $table->string('status', 12)->default('approved');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'status']);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->string('season_override', 4)->nullable()->after('season_ends_on');
        });
    }

    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('season_override');
        });
        Schema::dropIfExists('search_aliases');
    }
};
