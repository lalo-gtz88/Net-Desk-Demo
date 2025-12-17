<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lastname')->nullable();
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(1);
            $table->string('password');
            $table->timestamps();
            $table->string('telegram', 50)->nullable();
            $table->string('photo')->default('user.png');
            $table->boolean('tecnico')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
