<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('nombre_completo')->virtualAs('nombre || \' \' || apellido'); // SQLite/Postgres compatible
            $table->string('tipo_documento')->nullable();
            $table->string('documento')->nullable();
            $table->string('cargo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->foreignId('sede_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Unique constraints for deduplication logic
            $table->unique('email');
            $table->unique(['tipo_documento', 'documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
