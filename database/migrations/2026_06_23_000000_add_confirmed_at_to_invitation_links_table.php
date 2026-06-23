<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WhatsApp invitations are created in a "pending" state (confirmed_at = null)
     * and only count toward a user's daily limit once the sender confirms the
     * message was actually sent. Pre-existing links are treated as confirmed so
     * historical data keeps counting as before.
     */
    public function up(): void
    {
        Schema::table('invitation_links', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('via');
        });

        DB::table('invitation_links')
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_links', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');
        });
    }
};
