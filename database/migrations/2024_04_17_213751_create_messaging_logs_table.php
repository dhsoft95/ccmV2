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
        Schema::create('messaging_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supporter_id');
            $table->foreign('supporter_id')->references('id')->on('supporters')->onDelete('cascade');
            $table->string('channel');
            $table->boolean('success')->default(false);
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messaging_logs');
    }
};
