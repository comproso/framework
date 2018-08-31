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
            $table->string('name')->nullable();
            $table->enum('type', ['test', 'project'])->default('project');
            $table->integer('repetitions')->unsigned()->nullable()->default(1);
            $table->timestamp('repetitions_interval')->nullable();
			$table->timestamp('time_limit')->nullable();
            $table->boolean('continueable')->nullable()->default(false);
            $table->boolean('reporting')->nullable()->default(true);
            $table->boolean('caching')->nullable()->default(true);
            $table->boolean('active')->nullable()->default(false);
            $table->text('assets')->nullable()->default(null);
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
        Schema::dropIfExists('tests');
    }
}
