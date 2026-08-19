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
        Schema::create('signal_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->timestamp('recorded_at')->useCurrent()->index();

            // Core Signal Metrics (Decibels)
            $table->decimal('rsrp', 6, 2)->comment('Reference Signal Received Power (dBm) - e.g. -88.00');
            $table->decimal('rssi', 6, 2)->comment('Received Signal Strength Indicator (dBm) - e.g. -62.00');
            $table->decimal('rsrq', 6, 2)->comment('Reference Signal Received Quality (dB) - e.g. -12.00');
            $table->decimal('sinr', 6, 2)->comment('Signal to Interference plus Noise Ratio (dB) - e.g. 14.00');

            // LTE Radio Carrier Parameters
            $table->string('band', 20)->default('B40')->comment('LTE Band - e.g. B40, B3, B1');
            $table->string('bandwidth', 20)->default('20 MHz')->comment('Channel Bandwidth - e.g. 20 MHz');
            $table->unsignedInteger('earfcn')->default(39146)->comment('E-UTRA Absolute Radio Frequency Channel Number');
            $table->string('transmission_mode', 20)->default('TM8')->comment('Transmission Mode - e.g. TM8, TM7');
            $table->decimal('tx_power', 6, 2)->default(23.00)->comment('Transmit Power (dBm)');
            $table->string('rrc_state', 30)->default('Connected')->comment('RRC State - Connected, Idle');
            $table->unsignedTinyInteger('mcs')->default(24)->comment('Modulation and Coding Scheme index (0-31)');
            $table->unsignedTinyInteger('cqi')->default(10)->comment('Channel Quality Indicator (1-15)');

            // Cell Tower Identifiers
            $table->string('enodeb', 50)->default('2994')->comment('eNodeB Base Station ID');
            $table->string('cell_id', 50)->default('2')->comment('Local Cell / Sector ID');
            $table->string('global_cell_id', 50)->default('BB202')->comment('E-UTRAN Global Cell Identifier (ECI)');
            $table->string('physical_cell_id', 50)->default('11')->comment('Physical Cell ID (PCI)');

            // Calculated Signal Quality Level (Poor, Fair, Good, Very Good, Excellent)
            $table->string('overall_quality', 30)->default('Good');
            $table->unsignedTinyInteger('signal_score')->default(80)->comment('0-100 score');

            $table->timestamps();

            // Compound Indexes for fast historical time-series queries
            $table->index(['router_id', 'recorded_at']);
            $table->index(['router_id', 'rsrp']);
            $table->index(['router_id', 'sinr']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signal_readings');
    }
};
