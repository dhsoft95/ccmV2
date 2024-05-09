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
        Schema::create('supporters', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('gander')->nullable();
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('village_id');
            $table->unsignedBigInteger('ward_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('candidate_id');
            $table->string('phone_number')->nullable();
            $table->boolean('promised')->default(false);
            $table->text('other_supporter_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporters');
    }
};
