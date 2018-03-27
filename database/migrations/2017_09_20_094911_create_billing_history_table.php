<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('paid_feature_id')->nullable();
            $table->dateTime('billing_date');
            $table->string('amount');
            $table->text('description');
            $table->char('notification_status',4)->default('P')->comment('P for pending, R for read');
            $table->char('payment_status', 4);
            $table->string('payment_status_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_history');
    }
}
