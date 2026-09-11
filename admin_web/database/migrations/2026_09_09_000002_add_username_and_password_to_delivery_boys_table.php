<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->unique()->after('license_number');
            $table->string('password')->nullable()->after('username');
        });

        // Set default username and password for existing delivery boys
        $defaultPassword = Hash::make('delivery@123');
        $existing = DB::table('delivery_boys')->get();

        foreach ($existing as $boy) {
            $baseUsername = strtolower(explode(' ', trim($boy->name))[0]);
            $username = $baseUsername;
            $count = 1;
            while (DB::table('delivery_boys')->where('username', $username)->where('id', '!=', $boy->id)->exists()) {
                $username = $baseUsername . $count;
                $count++;
            }

            DB::table('delivery_boys')->where('id', $boy->id)->update([
                'username' => $username,
                'password' => $defaultPassword,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn(['username', 'password']);
        });
    }
};
