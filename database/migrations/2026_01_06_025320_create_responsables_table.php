<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection(config('database.default'))->getDriverName();

        Schema::create('responsables', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            if ($driver === 'pgsql') {
                $table->string('nombre_completo')->storedAs("nombre || ' ' || apellido");
            } else {
                $table->string('nombre_completo')->virtualAs("nombre || ' ' || apellido");
            }
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
