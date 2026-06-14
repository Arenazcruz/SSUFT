<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'foto_perfil')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->string('foto_perfil')->nullable()->after('avatar_color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'foto_perfil')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->dropColumn('foto_perfil');
            });
        }
    }
};
