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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('public_uid')->unique(); // For public customer access
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_record_id')->constrained()->cascadeOnDelete();
            $table->string('quote_number'); // Human readable (ex: DEV-100)
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired, invoiced
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax1_amount', 15, 2)->default(0);
            $table->decimal('tax2_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            $table->date('expire_at')->nullable();
            $table->text('notes')->nullable(); // Internal notes
            $table->text('terms')->nullable(); // Customer terms
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
