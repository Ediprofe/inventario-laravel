<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_ajuste_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_inventario_id')->constrained('envios_inventario')->cascadeOnDelete();
            $table->foreignId('responsable_id')->constrained('responsables')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicacions')->nullOnDelete();
            $table->string('tipo_solicitud', 30)->default('ajuste_general');
            $table->string('estado', 20)->default('pendiente');
            $table->string('solicitante_nombre', 120);
            $table->string('medio_contacto', 20)->nullable();
            $table->string('contacto_detalle', 160)->nullable();
            $table->string('franja_horaria', 120)->nullable();
            $table->text('detalle');
            $table->boolean('confirmado_coordinacion')->default(false);
            $table->timestamp('solicitado_at');
            $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->text('observacion_admin')->nullable();
            $table->timestamps();

            $table->index(['estado', 'solicitado_at']);
            $table->index(['responsable_id', 'solicitado_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_ajuste_inventario');
    }
};
