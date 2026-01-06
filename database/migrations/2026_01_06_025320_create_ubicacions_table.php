<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo');
            $table->string('tipo'); // Enum TipoUbicacion
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->nullOnDelete();
            $table->integer('piso')->nullable();
            $table->integer('capacidad')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['sede_id', 'nombre']);
            $table->unique(['sede_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacions');
    }
};
