<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaidFeatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paid_feature', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('feature_id')->nullable();
            $table->enum('payment_type', ['online', 'scratch_card']);
            $table->string('scratch_card')->nullable();
            $table->string('txn_id')->nullable()->comment('The CoinPayments transaction ID');
            $table->integer('total_resource')->unsigned();
            $table->integer('remain_resoure')->unsigned();
            $table->dateTime('active_date');
            $table->dateTime('purchase_date');
            $table->dateTime('expire_date');
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
        Schema::dropIfExists('paid_feature');
    }
}
