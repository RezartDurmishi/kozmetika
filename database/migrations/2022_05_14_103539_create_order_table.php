<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('quantity')->nullable(false);
            $table->date('orderDate')->nullable(false);
            $table->string('address')->nullable(false);
            $table->string('status')->nullable(true);
            $table->bigInteger('price');
            $table->bigInteger('total');
            $table->unsignedBigInteger('parent_id')->nullable(true);
            $table->unsignedBigInteger('product_id')->nullable(true);
            $table->unsignedBigInteger('user_id')->nullable(false);

            //foreign keys
            $table->foreign('parent_id')->references('id')->on('orders');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
