<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reuniones', 'jitsi_room')) {
            Schema::table('reuniones', function (Blueprint $table): void {
                $table->string('jitsi_room')->nullable()->unique()->after('access_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reuniones', 'jitsi_room')) {
            Schema::table('reuniones', function (Blueprint $table): void {
                $table->dropUnique('reuniones_jitsi_room_unique');
                $table->dropColumn('jitsi_room');
            });
        }
    }
};
