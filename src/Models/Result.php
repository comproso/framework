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
 *	@copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>
 *	@license AGPL-3.0
 *
 */

namespace Comproso\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Comproso\Framework\Traits\ModelTrait;
use Comproso\Framework\Traits\ModelHelperTrait;

class Result extends Model
{
	use ModelTrait;

    // table
    protected $table = 'results';

    // whitelist
    protected $fillable = ['user_id', 'item_id', 'value'];

	// Item Model
    public function item()
    {
	    return $this->belongsTo($this->ItemModel);
    }

    // User Model
    public function user()
    {
	    return $this->belongsTo($this->UserModel);
    }

    // Test Model
    public function test()
    {
	    return $this->belongsTo($this->TestModel);
    }


}
