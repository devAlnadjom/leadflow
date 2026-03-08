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
        Schema::table('lead_records', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Set the appropriate company_id for existing records based on their lead_form
        if (Schema::hasColumn('lead_records', 'company_id') && Schema::hasColumn('lead_records', 'lead_form_id')) {
            \Illuminate\Support\Facades\DB::update('UPDATE lead_records lr INNER JOIN lead_forms lf ON lr.lead_form_id = lf.id SET lr.company_id = lf.company_id');
        }

        // Now make it not nullable if possible, although for SQLite it might be tricky. Nullable is fine for now but let's enforce foreign key.
        // Actually to make it non-nullable in MySQL we would do:
        // Schema::table('lead_records', function (Blueprint $table) {
        //     $table->unsignedBigInteger('company_id')->nullable(false)->change();
        // });
        // But let's leave it simply as nullable to avoid migration issues with different DB drivers mapping.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_records', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
