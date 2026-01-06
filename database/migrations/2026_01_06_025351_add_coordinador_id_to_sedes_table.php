<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sedes', function (Blueprint $table) {
            $table->foreignId('coordinador_id')->nullable()->constrained('responsables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sedes', function (Blueprint $table) {
            $table->dropForeign(['coordinador_id']);
            $table->dropColumn('coordinador_id');
        });
    }
};
