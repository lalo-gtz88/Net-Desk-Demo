<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->text('notas')->nullable();
            $table->string('ticket');
            $table->integer('usuario');
            $table->string('file')->nullable();
            $table->binary('print')->nullable();
            $table->binary('unido')->nullable();
            $table->integer('id_diagnostico')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
