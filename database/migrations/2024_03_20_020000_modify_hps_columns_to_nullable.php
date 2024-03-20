<?php

use App\Application;
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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('hps_client_id')->nullable()->default(null)->change();
            $table->string('hps_client_secret')->nullable()->default(null)->change();
        });

        Application::chunk(100, function ($applications) {
            foreach ($applications as $application) {
                $application->hps_client_id = null;
                $application->hps_client_secret = null;
                $application->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Application::chunk(100, function ($applications) {
            foreach ($applications as $application) {
                $application->hps_client_id = '';
                $application->hps_client_secret = '';
                $application->save();
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('hps_client_id')->nullable(false)->default('')->change();
            $table->string('hps_client_secret')->nullable(false)->default('')->change();
        });
    }
};
