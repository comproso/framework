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

use Auth;
use Validator;

use Maatwebsite\Excel\Facades\Excel;

use Comproso\Framework\Traits\ModelTrait;
use Comproso\Framework\Models\Result;

class Test extends Model
{
	use ModelTrait;

    // table
    protected $table = 'tests';

    // whitelist
    protected $fillable = [];

    // pages
    public function pages()
    {
	    return $this->hasMany($this->PageModel);
    }

    // items
    public function items()
    {
	    return $this->hasManyThrough($this->ItemModel, $this->PageModel);
    }

    // results
    public function results()
    {
	    return $this->hasMany($this->ResultModel);
    }

    // users
    public function users()
    {
	    return $this->belongsToMany($this->UserModel)->withPivot('page_id')->withPivot('repetitions')->withPivot('finished')->withTimestamps();
    }

    /*
	 *	special Test functionalities
	 *
	 */

	// integrate another test
	public function integrate(Test $test, $pageStartPos = null)
	{
		if(is_null($pageStartPos))
			$pageStartPos = $this->pages()->count() + 1;
		else
		{
			if($this->pages()->count() > $pageStartPos)
			{
				$count = $test->pages()->count();

				// update pages
				$this->pages()->orderBy('position')->where('position', '>', $pageStartPos)->update(['position' => 'position + '.$count]);
			}
			else
				$pageStartPos = 1;
		}

		$pages = $test->pages()->with('items')->orderBy('position')->get();

		// create pages
		foreach($pages as $page)
		{
			$newPage = $page->replicate(['id', 'test_id']);
			$newPage->test_id = $this->id;
			$newPage->save();

			$items = [];

			foreach($page->items as $item)
			{
				$newItem = $item->replicate(['id', 'element_id']);

				$element = $item->element->replicate(['id']);
				$element->save();

				$newItem->element_id = $element->id;

				$items[] = $newItem;
			}

			$newPage->items()->saveMany($items);

			$pageStartPos++;
		}

		return;
	}

	// import a test
	public function import($fileWithPath, $delimiter = ',')
	{
		$rows = Excel::setDelimiter($delimiter)->load($fileWithPath)->get();

		$pagePos = -99;
		$pageCounter = 1;

		foreach($rows as $row)
		{
			// create a new step
			if($pagePos != $row->page)
			{
				if($pagePos >= 1)
					$page->items()->saveMany($items);

				$page = new Page;

				if($row->model == 'Comproso\Framework\Models\Page')
				{
					$params = [];

					// input validation
					$validator = Validator::make($row->toArray(), [
						'page_recallable' => 'boolean',
						'page_returnable' => 'boolean',
						'page_template' => 'string',
						'page_operations_template' => 'string',
						'page_assets'	=> 'json'
					]);

					// validation fail
					if($validator->fails())
					{
						\Log::error($validator->errors());
						return false;
					}

					if(isset($row->page_recallable))
						$page->recallable = $row->page_recallable;

					if(isset($row->page_returnable))
						$page->returnable = $row->page_returnable;

					if(isset($row->page_template))
						$page->template = $row->page_template;

					if(isset($row->page_operations_template))
						$page->operations_template = $row->page_operations_template;

					if(isset($row->page_assets))
						$page->assets = $row->page_assets;
				}

				$page->position = $pageCounter;
				$this->pages()->save($page);

				$items = [];

				$pagePos = $row->page;
				$pageCounter++;
				$itemCounter = 1;

				if($row->model == 'Comproso\Framework\Models\Page')
					continue;
			}

			// create Item
			$item = new Item;
			$item->position = $itemCounter;
			$item->element_type = $row->model;

			// create Element
			$element = new $row->model;
			$element->implement($row);
			$element->save();

			$item->element_id = $element->id;

			$items[] = $item;

			$itemCounter++;
		}

		// save last page
		$page->items()->saveMany($items);
	}

	// add new results
	public function addResults($results)
	{
		$toSave = [];

		foreach($results as $iid => $result)
		{
			$toSave[] = new Result([
				'user_id'	=> Auth::user()->id,
				'item_id'	=> $iid,
				'value'		=> ((is_array($result)) OR (is_object($result))) ? json_encode($result): $result,
			]);

		}

		// save results
		$this->results()->saveMany($toSave);

		return true;
	}

	/*
	 *	test functionalities
	 */

	// define type
	public function scopeOfType($query, $type)
	{
		if(!in_array($type, ['test', 'project']))
			\Log::error('invalid test type.');

		return $query->where('type', $type);
	}

	// get active
	public function scopeIsActive($query)
	{
		return $query->where('active', true);
	}

	// project is continuable
	public function scopeIsContinueable($query)
	{
		return $query->where('conf_continueable', true);
	}

	/*
	 *		page functionalities
	 */

	// get current/present page (or first page)
	public function currentPage()
	{
		return $this->pages()->present();
	}

	// get next page
	public function nextPage()
	{
		return $this->pages()->following()->first();
	}

	// get previous page
	public function previousPage()
	{
		return $this->pages()->previous()->first();
	}
}
