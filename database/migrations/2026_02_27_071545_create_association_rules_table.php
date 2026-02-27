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
        Schema::create('association_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_run_id')->constrained()->cascadeOnDelete();
            $table->json('antecedent');
            $table->json('consequent');
            $table->float('support');
            $table->float('confidence');
            $table->float('lift');
            $table->float('conviction')->nullable();
            $table->timestamps();

            $table->index('lift');
            $table->index('confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('association_rules');
    }
};
