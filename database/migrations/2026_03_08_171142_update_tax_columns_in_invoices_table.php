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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax1_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('tax2_amount', 15, 2)->default(0)->after('tax1_amount');
            $table->dropColumn('tax_total');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax_total', 10, 2)->default(0)->after('subtotal');
            $table->dropColumn(['tax1_amount', 'tax2_amount']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0);
        });
    }
};
