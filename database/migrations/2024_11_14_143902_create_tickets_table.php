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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tema');
            $table->text('descripcion');
            $table->string('telefono');
            $table->string('departamento');
            $table->string('ip')->nullable();
            $table->integer('asignado')->default('0');
            $table->string('edificio')->nullable();
            $table->string('usuario_red')->nullable();
            $table->string('autoriza')->nullable();
            $table->integer('creador')->nullable();
            $table->string('prioridad')->default('Baja');
            $table->string('colorPrioridad')->default('success');
            $table->string('categoria')->nullable();
            $table->string('status')->nullable();
            $table->integer('usuario')->nullable();
            $table->string('reporta')->nullable();
            $table->text('comentarios_print')->nullable();
            $table->date('fecha_atencion')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('unidad_si_no')->nullable();
            $table->string('unidad')->nullable();
            $table->text('last_coment')->nullable();
            $table->string('user_coment')->nullable();
            $table->timestamp('date_coment')->nullable();
            $table->timestamp('date_close')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
