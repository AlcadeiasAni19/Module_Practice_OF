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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('is_active')->default(1)->comment('0:Inactive, 1:Active');
            $table->integer('is_nested')->default(1)->comment('0:Not Nested, 1:Nested');
            $table->unsignedBigInteger("tab_category_id")->nullable();
            $table->timestamps();

            $table->foreign('tab_category_id')->references('id')->on('tab_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
