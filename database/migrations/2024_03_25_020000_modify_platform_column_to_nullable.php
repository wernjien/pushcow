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
        if (Schema::hasColumn('devices', 'platform')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('platform')->nullable()->change();
            });
        } else {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('platform')->nullable()->after('application_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('platform')->nullable(false)->change();
        });
    }
};
