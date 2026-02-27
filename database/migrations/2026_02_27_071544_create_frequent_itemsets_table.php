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
        Schema::create('frequent_itemsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_run_id')->constrained()->cascadeOnDelete();
            $table->json('items');
            $table->unsignedInteger('item_count');
            $table->float('support');
            $table->unsignedInteger('transaction_count');
            $table->timestamps();

            $table->index('support');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frequent_itemsets');
    }
};
