<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFcmConfigToApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('server_key')->default('')->after('token');
            $table->string('sender_id')->default('')->after('server_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('server_key');
            $table->dropColumn('sender_id');
        });
    }
}
