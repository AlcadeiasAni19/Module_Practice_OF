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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->integer('role');
            $table->string('phone')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ["Male", "Female", "Others"])->nullable();
            $table->string('district')->nullable();
            $table->string('sub_district')->nullable();
            $table->integer('is_company')->nullable()->comment("1:Company, 2:Non-company");
            $table->integer('has_login_permission')->nullable()->comment("1:Can Login, 2:Can't Login");

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
