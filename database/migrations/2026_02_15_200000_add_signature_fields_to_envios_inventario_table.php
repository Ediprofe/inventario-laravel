<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios_inventario', function (Blueprint $table) {
            $table->string('firmante_nombre')->nullable()->after('ip_aprobacion');
            $table->longText('firma_base64')->nullable()->after('firmante_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('envios_inventario', function (Blueprint $table) {
            $table->dropColumn(['firmante_nombre', 'firma_base64']);
        });
    }
};
