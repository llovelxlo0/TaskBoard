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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('short_description', 255)->nullable()->after('title');
            $table->text('full_description')->nullable()->after('short_description');
            $table->string('priority', 32)->default('medium')->after('status');
            $table->timestamp('completed_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'full_description',
                'priority',
                'completed_at'
            ]);
        });
    }
};
