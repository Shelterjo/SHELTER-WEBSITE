<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A branch's location description in each language (M57 §10–§12): a nearby landmark as the Owner words it, shown on
 * the branch page and card. Empty and MISSING until the Owner saves it in the branch editor (saving = approval). It is
 * not the detailed street address (address_ar/en, PO-010) and never feeds schema streetAddress.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->string('landmark_ar', 300)->nullable()->after('address_en');
            $table->string('landmark_en', 300)->nullable()->after('landmark_ar');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropColumn(['landmark_ar', 'landmark_en']);
        });
    }
};
