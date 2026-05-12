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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('type')->default('document')->after('user_id');
            $table->integer('total_signers')->default(0)->after('status');
            $table->integer('completed_signers')->default(0)->after('total_signers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['type', 'total_signers', 'completed_signers']);
        });
    }
};
