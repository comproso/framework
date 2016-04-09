<?php
/**
 *	Comproso Framework.
 *
 *	This program is free software:
 *	you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation,
 *	either version 3 of the License, or (at your option) any later version.
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY;
 *	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *	See the GNU Affero General Public License for more details.
 *	You should have received a copy of the GNU Affero General Public License along with this program.
 *	If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>
 * @license AGPL-3.0
 *
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
