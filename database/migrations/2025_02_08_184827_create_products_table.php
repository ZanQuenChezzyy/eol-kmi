<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->foreignId('manufactur_id')->constrained('manufacturs')->cascadeOnDelete();
            $table->string('lisence_number', 45);
            $table->unsignedTinyInteger('duration')->length(1);
            $table->date('installed_at');
            $table->date('expired_at')->nullable();
            $table->unsignedTinyInteger('notified_at')->length(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
