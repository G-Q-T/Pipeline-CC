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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->longText('script_draft')->nullable();
            $table->string('format_type', 30);
            $table->string('status', 20)->default('idea')->index();
            $table->string('priority', 10)->default('medium')->index();
            $table->date('scheduled_date')->nullable()->index();
            $table->date('published_date')->nullable();
            $table->string('published_url', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
