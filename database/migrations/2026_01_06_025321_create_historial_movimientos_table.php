<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users'); // Who performed action (User or Responsable? User typically)
            // Let's assume User (auth system) for now. If it's the responsable of the item, it's different.
            // In Django it was 'responsable' (User).
            // Let's use nullable FK to users table (default Laravel User).
            $table->string('tipo_movimiento');
            $table->json('detalles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_movimientos');
    }
};
