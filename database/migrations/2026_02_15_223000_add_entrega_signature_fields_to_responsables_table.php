<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->boolean('es_firmante_entrega')->default(false)->after('activo');
            $table->string('firma_entrega_path')->nullable()->after('es_firmante_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->dropColumn(['es_firmante_entrega', 'firma_entrega_path']);
        });
    }
};
