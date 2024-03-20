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
            $table->string('api_version')->nullable()->after('token');
            $table->string('service_account')->nullable()->after('api_version');
        });

        Application::chunk(100, function ($applications) {
            foreach ($applications as $application) {
                $application->api_version = 2;
                $application->save();
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('api_version')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('api_version');
            $table->dropColumn('service_account');
        });
    }
};
