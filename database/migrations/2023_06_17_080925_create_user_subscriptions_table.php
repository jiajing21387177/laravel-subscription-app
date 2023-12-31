<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_plan_id');
            $table->dateTime('subscription_start_datetime')->nullable();
            $table->dateTime('subscription_end_datetime')->nullable();
            $table->string('stripe_subscription_id');
            $table->string('stripe_invoice_id');
            $table->enum('payment_status', ['checkout', 'pending', 'paid', 'failed'])->default('checkout');
            $table->boolean('is_canceled')->default(false)->comment('To recognize the user has discontinue the subscription.');
            $table->timestamps();

            $table->unique(['stripe_subscription_id', 'stripe_invoice_id'], 'stripe_subscription_invoice');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}
