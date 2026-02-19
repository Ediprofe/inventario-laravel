<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained();
            $table->foreignId('sede_id')->constrained();
            $table->foreignId('ubicacion_id')->constrained('ubicacions');
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->nullOnDelete();

            $table->string('placa')->nullable(); // Unique handled by index below
            $table->string('marca')->nullable();
            $table->string('serial')->nullable();

            $table->string('estado'); // Enum EstadoFisico
            $table->string('disponibilidad'); // Enum Disponibilidad

            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('placa');
            $table->index('serial');

            // Placa unique where not null/empty (NA)
            // Note: Partial indexes are DB-specific.
            // For universal support (SQLite/Postgres), checking manually or unique() nullable is standard.
            // But we have "NA" logic. If null, strict unique allows multiple nulls.
            // If "NA" string, we allow duplicates.
            // If not null and not "NA", must be unique.
            // Enforcing this via DB constraint is tricky in generic migration.
            // We'll rely on Application Logic (Model Observer/Validation) + Standard Index.
            // We adding a simple index here.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
