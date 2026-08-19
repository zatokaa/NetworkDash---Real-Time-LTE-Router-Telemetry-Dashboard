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
        Schema::create('connection_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50)->index(); // band_changed, cell_changed, signal_weak, signal_excellent, connected, disconnected
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('previous_value')->nullable();
            $table->string('new_value')->nullable();
            $table->enum('severity', ['info', 'warning', 'danger', 'success'])->default('info');
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['router_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_events');
    }
};
