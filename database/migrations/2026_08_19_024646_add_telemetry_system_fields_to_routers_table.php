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
        Schema::table('routers', function (Blueprint $table) {
            $table->string('config_version', 50)->nullable()->after('modem_version');
            $table->string('system_uptime', 100)->nullable()->after('build_date');
            $table->string('load_average', 50)->nullable()->after('system_uptime');
            $table->string('connection_time', 100)->nullable()->after('load_average');
            $table->string('network_mode', 30)->default('4G')->after('connection_time');
            $table->string('mode_status', 30)->default('Connected')->after('network_mode');
            $table->string('cs_status', 50)->default('No Service')->after('mode_status');
            $table->string('ps_status', 100)->default('Registered, the local network')->after('cs_status');
            $table->string('eps_status', 100)->default('Registered, the local network')->after('ps_status');
            $table->string('plmn', 50)->default('41311 / Dialog')->after('eps_status');
            $table->string('wan_ip', 50)->nullable()->after('plmn');
            $table->string('wan_gateway', 50)->nullable()->after('wan_ip');
            $table->string('wan_dns', 100)->nullable()->after('wan_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn([
                'config_version',
                'system_uptime',
                'load_average',
                'connection_time',
                'network_mode',
                'mode_status',
                'cs_status',
                'ps_status',
                'eps_status',
                'plmn',
                'wan_ip',
                'wan_gateway',
                'wan_dns',
            ]);
        });
    }
};
