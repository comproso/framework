<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *	Test table representation.
 *
 *	This file provides the default database table for tests (c.f., laravel migrazions).
 *
 *  @see Comproso\Framework\Models\Test for further information about Tests.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 * @license AGPL-3.0
 *
 */
class CreateTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('type', ['test', 'project']);
            $table->integer('repetitions')->unsigned()->default(1);
            $table->timestamp('repetitions_interval');
			$table->timestamp('time_limit');
            $table->boolean('continueable')->default(false);
            $table->boolean('reporting')->default(true);
            $table->boolean('caching')->default(true);
            $table->boolean('active')->default(false);
            $table->text('assets')->nullable()->default(null);
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
        Schema::dropIfExists('tests');
    }
}
