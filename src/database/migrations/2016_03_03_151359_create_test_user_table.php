<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *	Test*User table representation.
 *
 *	This file provides the default database table for Test and User relations (c.f., laravel migrazions).
 *
 *  @see Comproso\Framework\Models\Test for further information about Tests.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 * @license AGPL-3.0
 *
 */
class CreateTestUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('page_id')->unsigned()->nullable()->default(null);
            $table->integer('page_repetitions')->unsigned()->default(0);
            $table->integer('repetitions')->unsigned()->default(0);
            $table->boolean('started')->default(false);
            $table->boolean('finished')->default(false);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_user');
    }
}
