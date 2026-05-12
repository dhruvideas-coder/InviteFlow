<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->decimal('width_percent', 8, 4)->default(22)->after('y_percent');
            $table->decimal('height_percent', 8, 4)->default(4)->after('width_percent');
        });
    }

    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->dropColumn(['width_percent', 'height_percent']);
        });
    }
};
