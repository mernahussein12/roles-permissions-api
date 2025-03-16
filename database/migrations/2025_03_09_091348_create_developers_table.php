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
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('project_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('project_leader')->constrained('users');
            $table->json('team'); // Selected from users table (multiple team members)
            $table->foreignId('support')->constrained('users');
            $table->string('summary'); // PDF file path
            $table->decimal('cost', 10, 2);
            $table->decimal('profit_margin', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
