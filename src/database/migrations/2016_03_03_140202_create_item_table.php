<?php

/**
 *	Item table representation.
 *
 *	This file provides the default database table for test Items (c.f., laravel migrazions).
 *
 *  @see Comproso\Framework\Models\Item for further information about Items.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 * @license AGPL-3.0
 *
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->unsigned();
            $table->morphs('element');
            $table->integer('position')->unsigned();
            #$table->integer('proceeding_position')->unsigned();
            $table->string('name')->nullable();
            $table->boolean('proceed')->nullable();
            $table->string('template')->nullable();
            $table->string('cssId')->nullable();
            $table->string('cssClass')->nullable();
            $table->string('validation')->nullable();
            $table->nullableTimestamps();
            #$table->unique(['page_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
