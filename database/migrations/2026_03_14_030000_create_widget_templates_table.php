<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('general'); // general, construction, esthetic, real_estate, auto, service
            $table->string('icon')->default('📋');          // emoji
            $table->string('description')->nullable();
            $table->string('layout_mode')->default('stack'); // stack | slider
            $table->string('submit_button_label')->default('Envoyer');
            $table->json('fields');                          // snapshot des champs
            $table->boolean('is_system')->default(false);   // true = livré par défaut
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_templates');
    }
};
