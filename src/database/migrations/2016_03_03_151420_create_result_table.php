<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->unsigned();
            $table->integer('test_repetition_counter')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('page_repetition_counter');
            $table->text('values');
            $table->text('process_data');
            $table->integer('server_time_delta')->unsigned();
            $table->integer('user_time_delta')->unsigned();
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
        Schema::dropIfExists('results');
    }
}
