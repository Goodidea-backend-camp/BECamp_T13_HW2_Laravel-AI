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
        Schema::table('users', function (Blueprint $table) {
            $table->text('self_profile');
            $table->string('profile_image_path');
            $table->enum('provider',['local','google'])->default('local');
            $table->boolean('is_premium')->default(false);
            $table->unsignedBigInteger('current_thread')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
