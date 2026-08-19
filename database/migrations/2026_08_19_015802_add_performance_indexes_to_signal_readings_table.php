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
        Schema::table('signal_readings', function (Blueprint $table) {
            $table->index(['router_id', 'overall_quality'], 'idx_signal_router_quality');
            $table->index(['router_id', 'band'], 'idx_signal_router_band');
            $table->index(['cell_id', 'enodeb'], 'idx_signal_cell_enodeb');
        });

        Schema::table('routers', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'idx_routers_user_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signal_readings', function (Blueprint $table) {
            $table->dropIndex('idx_signal_router_quality');
            $table->dropIndex('idx_signal_router_band');
            $table->dropIndex('idx_signal_cell_enodeb');
        });

        Schema::table('routers', function (Blueprint $table) {
            $table->dropIndex('idx_routers_user_active');
        });
    }
};
