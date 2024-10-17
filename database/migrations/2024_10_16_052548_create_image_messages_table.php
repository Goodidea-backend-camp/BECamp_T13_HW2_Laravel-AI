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
        Schema::create('image_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('role',['user','assistant']);
            $table->text('content');
            $table->string('image_file_path');
            $table->timestamps();
            $table->foreignId('thread_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_messages');
    }
};
