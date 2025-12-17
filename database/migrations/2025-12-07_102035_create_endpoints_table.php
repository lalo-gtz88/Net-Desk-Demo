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
        Schema::create('endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable()->comment('Enlace WAN sucursal, dispositivo o servidor');
            $table->unsignedInteger('ip')->nullable()->comment('Dirección IP monitoreable');
            $table->enum('tipo', ['enlace', 'dispositivo', 'wan', 'otros'])->nullable()->comment('Enlace, dispositivo, WAN, etc.');
            $table->string('ubicacion', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('last_status', 50)->nullable();
            $table->integer('fails_count')->nullable();
            $table->tinyInteger('enviar_alerta')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
