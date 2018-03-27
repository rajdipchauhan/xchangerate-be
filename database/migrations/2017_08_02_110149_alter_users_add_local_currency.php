<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddLocalCurrency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
}
