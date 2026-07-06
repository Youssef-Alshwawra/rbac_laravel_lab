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
        Schema::create('agents', function (Blueprint $table) {
            // $table->id();
            $table->bigInteger('id');
            $table->unsignedBigInteger('parent_agent_id')->nullable();
            
            $table->string('name', 255);
            $table->string('email', 125)->unique();

            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->decimal('credit_used', 15, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_agent_id')->on('agent')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
