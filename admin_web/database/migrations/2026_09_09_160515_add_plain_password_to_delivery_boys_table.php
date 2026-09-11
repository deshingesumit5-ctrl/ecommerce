<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->string('plain_password')->nullable()->after('password');
        });

        // Set plain_password for existing delivery boys
        DB::table('delivery_boys')->where('username', 'arjun_16')->update(['plain_password' => 'arjun@16']);
        DB::table('delivery_boys')->where('username', 'rohan')->update(['plain_password' => 'delivery@123']);
        DB::table('delivery_boys')->whereNull('plain_password')->update(['plain_password' => 'delivery@123']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn('plain_password');
        });
    }
};
