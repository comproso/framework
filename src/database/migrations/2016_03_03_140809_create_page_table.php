<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *	Page table representation.
 *
 *	This file provides the default database table for test pages (c.f., laravel migrazions).
 *
 *  @see Comproso\Framework\Models\Page for further information about Pages.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 * @license AGPL-3.0
 *
 */
class CreatePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_id')->unsigned();
            $table->integer('position')->unsigned();
            #$table->boolean('repeat')->default(false);
            $table->integer('repetitions')->unsigned()->default(0);
            $table->integer('repetition_interval')->unsigned()->nullable()->default(null);
            $table->boolean('recallable')->default(false);
            $table->boolean('returnable')->default(false);
            $table->boolean('finish')->default(false);
            $table->integer('time_limit')->unsigned()->nullable()->default(null);
            $table->string('template')->default('comproso::page');
            $table->string('operations_template')->default('comproso::pages.op_fwd')->nullable();
            $table->text('assets')->nullable()->default(null);
            $table->boolean('default_assets')->default(true);
            $table->boolean('group_generate')->default(false);
            $table->boolean('group_proceed')->default(false);
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
        Schema::dropIfExists('pages');
    }
}
