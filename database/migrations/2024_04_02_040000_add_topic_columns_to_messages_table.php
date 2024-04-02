<?php

use App\Message;
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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('id')->constrained();
            $table->string('topic')->nullable()->after('application_id');
        });

        Message::withoutGlobalScope('device')->chunk(500, function ($messages) {
            foreach ($messages as $message) {
                $message->application_id = $message->device->application_id;
                $message->save();
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable(false)->change();
            $table->unsignedBigInteger('device_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['application_id']);

            $table->dropColumn('application_id');
        });
    }
};
