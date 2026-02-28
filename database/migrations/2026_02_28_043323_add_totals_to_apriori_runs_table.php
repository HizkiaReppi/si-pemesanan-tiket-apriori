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
        Schema::table('apriori_runs', function (Blueprint $table) {
            $table->unsignedInteger('total_frequent_itemsets')->default(0)->after('total_items');
            $table->unsignedInteger('total_rules')->default(0)->after('total_frequent_itemsets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apriori_runs', function (Blueprint $table) {
            $table->dropColumn(['total_frequent_itemsets', 'total_rules']);
        });
    }
};
