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
        Schema::table('users', function (Blueprint $table) {
            // user subscription type
            $table->tinyInteger('subscription_type')->default(0)->after('email_verified_at');
            // subscription ends time
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription_type');
            $table->dropColumn('subscription_ends_at');
        });
    }
};
