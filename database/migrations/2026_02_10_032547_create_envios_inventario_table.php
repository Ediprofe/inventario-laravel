<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responsable_id')->constrained('responsables');
            $table->string('tipo'); // 'por_ubicacion' o 'por_responsable'
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicacions');
            $table->string('email_enviado_a');
            $table->timestamp('enviado_at');
            $table->string('token')->unique();
            $table->timestamp('aprobado_at')->nullable();
            $table->string('ip_aprobacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios_inventario');
    }
};
