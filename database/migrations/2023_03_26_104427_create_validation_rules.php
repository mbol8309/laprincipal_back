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
        Schema::create('sys_validation_rule', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('field_name');
            $table->string('rule_name');
            $table->string('rule_parameters')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_validation_rule');
    }
};
