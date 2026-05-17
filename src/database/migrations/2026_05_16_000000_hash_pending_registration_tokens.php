<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pending_registrations', 'token')) {
            return;
        }

        if (Schema::hasColumn('pending_registrations', 'token_hash')) {
            DB::table('pending_registrations')->delete();

            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->dropColumn('token');
            });

            return;
        }

        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->after('email_hash');
        });

        DB::table('pending_registrations')->delete();

        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pending_registrations', 'token_hash')) {
            return;
        }

        Schema::table('pending_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('pending_registrations', 'token')) {
                $table->string('token', 64)->nullable()->after('email_hash');
            }

            $table->dropColumn('token_hash');
        });
    }
};
