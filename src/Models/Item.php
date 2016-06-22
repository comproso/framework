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


namespace Comproso\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Comproso\Framework\Traits\ModelTrait;

class Item extends Model
{
    // table
    protected $table = 'items';

    // whitelist
    protected $guarded = ['results', 'template', 'cssid', 'cssclass', 'name'];

    // JSON protection
    protected $hidden = ['template', 'validation', 'cssid', 'cssclass'];

    // page
    public function page()
    {
	    return $this->belongsTo($this->PageModel);
    }

    // elements
    public function element()
    {
	    return $this->morphTo();
    }

    // results
    public function results()
    {
	    return $this->belongsTo($this->ResultModel);
    }

    /*
	 *	special Item functionalities
	 *
	 */

	// element implementation
	public function implement($data)
	{
		return $this->element()->implement($data);
	}

	// element generation
	public function generate($cache)
	{
		$item = new Item;
		$item->results = $this->element->generate($cache);
		$item->name = 'item'.$this->id;
		$item->template = $this->template;
		$item->cssid = $this->cssId;
		$item->cssclass = $this->cssClass;

		return $item;
	}

	// element proceeding
	public function proceed($cache = null)
	{
		return $this->element->proceed($cache);
	}

	// element finish
	public function finish()
	{
		if(method_exists($this->element, 'finish'))
			$this->element->finish();
	}

/*
	// export an item (headline)
	public function export()
	{
		return (method_exists($this->element, 'export')) ? array_map(function ($item) {
			return 'item'.$this->id."_".$item;
		}, $this->element->export()) : null;
	}
*/
}
