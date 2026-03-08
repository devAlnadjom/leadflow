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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('default_tax_rate');
            
            $table->string('tax1_name')->nullable()->default('TPS');
            // Pour le taux jusqu'à 3 chiffres après la virgule (ex: 9.975 ou 5.000)
            // On peut utiliser (6, 3) : 3 chiffres avant la virgule, 3 après
            $table->decimal('tax1_rate', 6, 3)->nullable()->default(5.000);
            
            $table->string('tax2_name')->nullable()->default('TVQ');
            $table->decimal('tax2_rate', 6, 3)->nullable()->default(9.975);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tax1_name',
                'tax1_rate',
                'tax2_name',
                'tax2_rate',
            ]);
            
            $table->decimal('default_tax_rate', 5, 2)->nullable();
        });
    }
};
