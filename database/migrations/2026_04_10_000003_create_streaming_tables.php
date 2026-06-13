<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('estado', 20)->default('programada')->index();
            $table->unsignedSmallInteger('duration_minutes')->default(90);
            $table->string('access_code', 20)->nullable();
            $table->string('cover_gradient')->nullable();
            $table->timestamps();
        });

        Schema::create('participantes_reunion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->constrained('reuniones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
            $table->unique(['reunion_id', 'user_id']);
        });

        Schema::create('chat_reunion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->constrained('reuniones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
        });

        Schema::create('grabaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->constrained('reuniones')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grabaciones');
        Schema::dropIfExists('chat_reunion');
        Schema::dropIfExists('participantes_reunion');
        Schema::dropIfExists('reuniones');
    }
};
