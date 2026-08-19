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
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->default('Home LTE Router');
            $table->string('model')->default('ZLT P11X');
            $table->string('ip_address')->nullable()->default('192.168.8.1');
            $table->string('firmware_version')->nullable()->default('6.4.2.25');
            $table->string('hardware_version')->nullable()->default('TZ7.821.172');
            $table->string('modem_version')->nullable()->default('P705A_1.0.9_210901');
            $table->string('build_date')->nullable()->default('2022-11-08');
            $table->string('imei')->nullable();
            $table->string('imsi')->nullable();
            $table->string('iccid')->nullable();
            $table->string('mac_address')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'idle', 'weak'])->default('connected');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
