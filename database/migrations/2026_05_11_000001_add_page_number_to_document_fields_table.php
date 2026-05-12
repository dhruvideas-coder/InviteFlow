<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->unsignedSmallInteger('page_number')->default(1)->after('y_percent');
        });
    }

    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->dropColumn('page_number');
        });
    }
};
