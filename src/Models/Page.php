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
use Illuminate\Http\Request;

use Auth;
use Cache;
use View;

use Comproso\Framework\Traits\ModelTrait;

class Page extends Model
{
	use ModelTrait;

    // table
    protected $table = 'pages';

    // whitelist
    protected $fillable = [];

    // Item model
    public function items()
    {
	    return $this->hasMany($this->ItemModel);
    }

    // Test Model
    public function test()
    {
	    return $this->belongsTo($this->TestModel);
    }

    /*
	 *	special Page functionalities
	 *
	 */

	// generation
	public function generate()
	{
		// get Items
		$items = $this->items()->orderBy('position')->get();

		// prepare results
		$results = [];

		foreach($items as $item)
		{
			if(Cache::has($item->id))
				$cache = Cache::get($item->id);
			else
				$cache = null;

			// store generated Item
			$results[] = $item->generate($cache);
		}

		return View::make($this->template, [
			'test_id'		=> $this->test_id,
			'page_id'		=> $this->id,
			'results'		=> $results,
			'nav'			=> $this->operations_template,
			'assets'		=> json_decode($this->assets)
		])->render();
	}

	// proceeding of data
	public function proceed(Request $request, $reporting = null, $caching = null)
	{
		$items = $this->items()->orderBy('position')->get();

		// prepare results
		$results = [];

		// validate and proceed items
		foreach($items as $item)
		{
			$itemResult = $item->proceed($request);

			if(!$itemResult)
				\Log::error(get_class($item)." (".$item->id."): invalid request data");
			else
				$results = array_merge($results, [$item->id => $itemResult]);
		}

		// get test
		$test = $this->test()->first();

		// check reporting config
		if(is_null($reporting))
			$reporting = $test->conf_reporting;

		// check caching config
		if(is_null($caching))
			$caching = $test->conf_caching;

		// report data
		if($reporting)
		{
			$test->addResults($results);
		}

		// cache data
		if($caching)
		{
			$dotResults = array_dot($results);

			foreach($dotResults as $item => $result)
			{
				Cache::put($item, $result);
			}
		}

		return true;
	}

	// present page
	public function scopePresent($query, $user = null)
	{
		if(is_null($user))
			$user = Auth::user();

		$pid = $user->tests('test_id', $this->test_id)->first()->pivot->page_id;

		if(is_null($pid))
			return $query->orderBy('position')->first();
		else
			return $query->find($pid);
	}

	// page with position X
	public function scopeOfPosition($query, $position = 1)
	{
		return $query->where('position', $position);
	}

	// next page
	public function scopeFollowing($query)
	{
		$pos = $this->present()->position;

		return $query->ofPosition(($pos + 1));
	}

	// previous page
	public function scopePrevious($query)
	{
		$pos = $this->present()->position;

		return $query->ofPosition(($pos - 1));
	}
}
