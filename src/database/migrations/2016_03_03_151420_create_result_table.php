<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *	Result table representation.
 *
 *	This file provides the default database table for Test Results (c.f., laravel migrazions).
 *
 *  @see Comproso\Framework\Models\Result for further information about Results.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 * @license AGPL-3.0
 *
 */
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
