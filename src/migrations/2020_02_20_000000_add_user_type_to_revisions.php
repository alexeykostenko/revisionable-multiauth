<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserTypeToRevisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('revisions')) {
            Schema::table('revisions', function (Blueprint $table) {
                $table->string('user_type')->after('revisionable_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('revisions')) {
            Schema::table('revisions', function (Blueprint $table) {
                $table->dropColumn(['user_type']);
            });
        }
    }
}
