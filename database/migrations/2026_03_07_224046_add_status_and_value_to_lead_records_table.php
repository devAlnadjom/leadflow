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
            $table->string('status')->default('new')->after('source');
            $table->decimal('value', 10, 2)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_records', function (Blueprint $table) {
            $table->dropColumn(['status', 'value']);
        });
    }
};
