<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion_inventario_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ubicacion_id')->constrained('ubicacions')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('tipo')->default('complementario');
            $table->string('archivo_pdf_path');
            $table->date('fecha_corte')->nullable();
            $table->string('responsable_fuente')->nullable();
            $table->boolean('adjuntar_en_envio')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion_inventario_anexos');
    }
};
