<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sso_provider')) {
                $table->string('sso_provider')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'sso_provider_id')) {
                $table->string('sso_provider_id')->nullable()->unique()->after('sso_provider');
            }
            if (! Schema::hasColumn('users', 'sso_token')) {
                $table->text('sso_token')->nullable()->after('sso_provider_id');
            }
            if (! Schema::hasColumn('users', 'sso_avatar')) {
                $table->string('sso_avatar')->nullable()->after('sso_token');
            }

            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_provider', 'sso_provider_id', 'sso_token', 'sso_avatar']);
        });
    }
};
