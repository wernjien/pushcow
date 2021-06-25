<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StringifyUserIdFromDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('user_id_string')->nullable()->after('user_id');
        });

        DB::statement('UPDATE `devices` SET `user_id_string` = `user_id`');

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->renameColumn('user_id_string', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_ulong')->nullable()->after('user_id');
        });

        DB::statement('UPDATE `devices` SET `user_id_ulong` = `user_id`');

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->renameColumn('user_id_ulong', 'user_id');
        });
    }
}
