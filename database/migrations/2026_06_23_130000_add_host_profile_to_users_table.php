<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-level "host / organizer" identity shown on top of every invitation
     * preview. Stored once per admin and reused across all of their documents;
     * members inherit their parent admin's host (see HostProfileController).
     *
     * The name is kept in both scripts so it can follow the document's
     * language (Gujarati documents show the Gujarati name, English the English).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('host_name_en')->nullable()->after('organization');
            $table->string('host_name_gu')->nullable()->after('host_name_en');
            $table->string('host_image_path')->nullable()->after('host_name_gu');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['host_name_en', 'host_name_gu', 'host_image_path']);
        });
    }
};
