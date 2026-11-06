<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_id', 100)->unique();
            $table->string('system_role', 20)->default('GENERAL');
            $table->boolean('is_leader')->default(false);
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropUnique(['login_id']);
            $table->dropColumn(['login_id', 'system_role', 'is_leader', 'is_active']);
        });
    }
};
