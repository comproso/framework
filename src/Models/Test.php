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
use Carbon\Carbon;
use Input;
use Request;
use Session;
use Validator;
use View;

use Maatwebsite\Excel\Facades\Excel;

use Comproso\Framework\Traits\ModelTrait;
use Comproso\Framework\Traits\ModelHelperTrait;
use Comproso\Framework\Models\Result;

class Test extends Model
{
	use ModelTrait, ModelHelperTrait;

	/**
	 *	define database table.
	 */
    protected $table = 'tests';

    /**
	 *	define mass assignable values.
	 */
    protected $fillable = ['name', 'active', 'type'];

    // dates
    protected $dates = ['time_limit', 'repetitions_interval'];

    // json protections
    #protected $hidden = ['results'];

    /**
	 *	define home uri.
	 */
    #protected $home = '/';


	/*
	|--------------------------------------------------------------------------
	| Relations to other models
	|--------------------------------------------------------------------------
	|
	| In this section, the relations to other Models of the framework, as well
	| as the User Model are defined.
	|
	*/

	/**
	 *	Page Model.
	 *
	 *	@return objects
	 */
    public function pages()
    {
	    return $this->hasMany($this->PageModel);
    }

    /**
	 *	Item Model.
	 *
	 *	@return objects
	 */
    public function items()
    {
	    return $this->hasManyThrough($this->ItemModel, $this->PageModel);
    }

    /**
	 *	Result Model.
	 *
	 *	@return objects
	 */
    public function results()
    {
	    return $this->hasManyThrough($this->ResultModel, $this->PageModel);
    }

    /**
	 *	User Model.
	 *
	 *	@return objects
	 */
    public function users()
    {
	    return $this->belongsToMany($this->UserModel)
	    			->withPivot('page_id')
	    			->withPivot('repetitions')
	    			->withPivot('page_repetitions')
	    			->withPivot('started')
	    			->withPivot('finished')
	    			->withTimestamps();
    }

    /*
	|--------------------------------------------------------------------------
	| Framework functions
	|--------------------------------------------------------------------------
	|
	| In this section, special functionalities of the comproso/framework are
	| defined.
	|
	*/

    /**
	 *	Test integration.
	 *
	 *	@return boolean
	 */
	public function integrate(Test $test, $pageStartPos = null, $save = true)
	{
		// count pages
		$count = $test->pages()->count();
		$currentCount = $this->pages()->count();

		if(is_null($pageStartPos))
		{
			// get last page
			$lastPage = $this->pages()->orderBy('position', 'desc')->first();

			// count current pages
			$pageStartPos = $currentCount;

			if(is_null($lastPage))
			{
				$pageStartPos = 1;
			}
			elseif($lastPage->finish)
			{
				$lastPage->position += $count;
				$lastPage->save();
			}
			else
				$pageStartPos++;
		}
		else
		{
			// page position
			if($pageStartPos < 0)
				$pageStartPos += $currentCount + 1;

			// update pages
			if($currentCount >= $pageStartPos)
				$this->pages()->orderBy('position')->where('position', '>=', $pageStartPos)->increment('position', $count);
			else
				$pageStartPos = 1;
		}

		$pages = $test->pages()->where('finish', false)->with('items')->orderBy('position')->get();

		// create pages
		foreach($pages as $page)
		{
			$newPage = $page->replicate(['id', 'test_id']);
			$newPage->test_id = $this->id;
			$newPage->position = $pageStartPos;
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

		// add assets
		$this->assets = $test->assets;

		// save if allowed
		if($save)
			$this->save();

		return true;
	}

	/**
	 *	Test import.
	 *
	 *	@return boolean
	 */
	public function import($fileWithPath, $delimiter = ',', $save = true)
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

				if(trim($row->model) == 'Comproso\Framework\Models\Page')
				{
					$params = [];

					// input validation
					/*$validator = Validator::make($row->toArray(), [
						'recallable' => 'boolean',
						'returnable' => 'boolean',
						'template' => 'string',
						'operations_template' => 'string',
						'page_assets'	=> 'json'
					]);

					// validation fail
					if($validator->fails())
					{
						\Log::error($validator->errors());
						return false;
					}*/

					// set page call settings
					$page->recallable = boolval($row->recallable);
					$page->returnable = boolval($row->returnable);

					if(!empty(trim($row->template)))
						$page->template = trim($row->template);

				}
				elseif(!empty(trim($row->page_template)))
					$page->template = trim($row->page_template);

				// set page assets
				if(!empty(trim($row->page_assets)))
					$page->assets = json_encode(json_decode(trim($row->page_assets)));

				// set operations template
				if(!empty(trim($row->operations_template)))
					$page->operations_template = ($row->operations_template == "null") ? null : trim($row->operations_template);

				$page->repetitions = (is_null($row->repetitions)) ? 0 : $row->repetitions;
				$page->repetition_interval = (is_null($row->interval)) ? null : $row->interval;
				$page->time_limit = (is_null($row->time_limit)) ? null : $row->time_limit;
				$page->position = $pageCounter;
				$this->pages()->save($page);

				$items = [];

				$pagePos = $row->page;
				$pageCounter++;
				$itemCounter = 1;

				if(trim($row->model) == 'Comproso\Framework\Models\Page')
					continue;
			}

			// create Item
			$item = new Item;
			$item->position = $itemCounter;
			$item->element_type = trim($row->model);

			// create Element
			$row->model = trim($row->model);
			$element = new $row->model;
			$element->implement($row);
			$element->save();

			$item->element_id = $element->id;

			$item->template = (empty(trim($row->template))) ? $element->template() : trim($row->template);

			$item->cssId = (empty(trim($row->cssid))) ? null : trim($row->cssid);
			$item->cssClass = (empty($row->cssclass)) ? null : trim($row->cssclass);
			$item->validation = (empty(trim($row->validation))) ? 'string' : trim($row->validation);

			$items[] = $item;

			$itemCounter++;
		}

		// save last page
		$page->items()->saveMany($items);

		// set test assets
		//$this->assets = (isset($testAssets)) ? json_encode($testAssets) : null;

		// save if allowed
		if($save)
			$this->save();

		return true;
	}

	/**
	 *	Test data export.
	 *
	 *	@return file
	 */
	public function export()
	{
		//
	}

	/**
	 *	Test content export.
	 *
	 *	@return file
	 */
	public function spawn()
	{
		//
	}

	/**
	 *	Check for continued.
	 *
	 *	@return
	 */
	protected function isContinued()
	{
		// TBD

		return false;
	}

	/**
	 *	Check for continued.
	 *
	 *	@return
	 */
	public function initialize()
	{
		if($this->isAuthGuarded())
		{
			if($this->user->pivot->finished)
				return new Page;

			// get first page
			$page = $this->pages()->orderBy('position')->with(['items' => function ($query) {
				$query->orderBy('position');
			}])->firstOrFail();

			// prepare database
			$this->users()->sync([$this->user->id => ['started' => true, 'page_id' => $page->id]], false);

			// prepare cache
			Session::put('test_id', $this->id);
			Session::put('page_id', $page->id);
			Session::put('user_id', $this->user->id);
			Session::put('test_repetition', $this->user->pivot->repetitions);
			Session::put('start_time_global', Carbon::now());
			Session::put('start_time_page', Carbon::now());
			Session::put('current_page', $page->id);
			Session::put('page_visit_counter', 0);

			return $page;
		}

		// prepare session
		// TBD
	}

	/**
	 *	Generate a test page.
	 *
	 *	@return object
	 */
	public function generate($pid = null)
	{
		\Log::debug(Session::get('page_visit_counter'));

		// interrupt if no data available
		if(is_null($this))
			return null;

		// display with user
		if($this->isAuthGuarded())
		{
			// check if test is a valid project
			if($this->type != "project")
				return new Page;

			// check if project is active
			if(!$this->active)
				return null;

			// abort if finished
			if($this->user->pivot->finished)
				return null;

			// start project
			if(!$this->user->pivot->started)
			{
				// initialize project (DB and Session)
				return $this->guarded()->initialize()->generate();
			}

			if(!$this->isContinued())
			{
				// do cache
				$pageVisits = Session::pull('page_visit_counter');
				Session::put('page_visit_counter', $pageVisits);
				Session::put('start_time_page', Carbon::now());
				Session::put('current_page', $this->user->pivot->page_id);

				// get current stored page
				return $this->pages()->orderBy('position')->with(['items' => function ($query) {
					$query->orderBy('position');
				}])->findOrFail($this->user->pivot->page_id)->generate();
			}
			elseif($this->continuable)
			{
				//
			}
			else
			{
				return null;
			}
		}

		if(is_null($pid))
		{
			return $this->pages()->orderBy('position')->with(['items' => function ($query) {
				$query->orderBy('position');
			}])->firstOrFail()->generate();
		}
		else
		{
			return $this->pages()->orderBy('position')->with(['items' => function ($query) {
				$query->orderBy('position');
			}])->findOrFail($pid)->generate();
		}
	}

	/**
	 *	Proceed a test page.
	 *
	 *	@return object
	 */
	public function proceed($pid = null)
	{
		// validate request
		if(!$this->validate())
			return null;

		// abort if limits are reached
		if((Request::wantsJson()) AND (!$this->reachedLimits(true)) AND ($this->reachedLimits()))
			return $this;

		// proceed with user
		if($this->isAuthGuarded())
		{
			// check if test starts
			if(is_null($this->user->pivot->page_id))
				return $this->initialize();

			// compare session and DB
			if(Session::get('page_id') != $this->user->pivot->page_id)
				Session::put('page_id', $this->user->pivot->page_id);

			if(Session::get('page_visit_counter') != $this->user->pivot->page_repetitions)
				Session::put('page_visit_counter', $this->user->pivot->page_repetitions);

			// get current page
			$page = $this->pages()->with(['items' => function ($query) {
				$query->orderBy('position');
			}])->findOrFail($this->user->pivot->page_id);

			if($page->proceed())
			{
				// prepare the page to display
				// check if previous, current, or next page
				if(boolval(Request::input('cctrl_prvs')) == true)
				{
					// check if leaving could be allowed
					if($page->returnable)
					{
						// get previous page candidate
						$prvsPage = $page->previous()->first();

						if((!is_null($prvsPage)) AND ($prvsPage->returnable))
						{
							// update page_id
							$this->users()->updateExistingPivot($this->user->id, ['page_id' => $prvsPage->id]);
							Session::put('page_id', $prvsPage->id);

							$page->finish(true);

							// return previous page
							return $prvsPage->generate();
						}
					}

					// if current page can be recalled
					#if($page->recallable)
					#	return $page->generate();
				}
			}
			else
			{
				// TBD

				// check if errors can be corrected
				if($page->recallable)
				{
					// update Session and DB
					$pageCounter = Session::pull('page_visit_counter');
					Session::put($pageCounter++);
					$this->users()->updateExistingPivot($this->user->id, ['page_id' => $nxtPage->id, 'page_repetitions' => $this->user->pivot->page_repetitions++]);

					return $page->generate()->withErrors();
				}
			}

			// check if it is a repeatable page
			if(($page->repetitions > 0) AND ($this->user->pivot->page_repetitions <= $page->repetitions))
			{
				// update Session and DB
				$pageCounter = Session::pull('page_visit_counter');
				$pageCounter++;

				#\Log::debug($page->repetitions);

				Session::put('page_visit_counter', $pageCounter);
				$this->users()->updateExistingPivot($this->user->id, ['page_repetitions' => $pageCounter]);

				// return current page
				return $page->generate();
			}

			// get next page
			$nxtPage = $page->next()->first();

			// finish if no next page exist
			if($nxtPage === null)
			{
				// set test to finished
				$this->users()->updateExistingPivot($this->user->id, ['finished' => true]);

				// clear session
				Session::flush();
				Session::regenerate();

				return new Page;
			}

			// update page_id
			$this->users()->updateExistingPivot($this->user->id, ['page_id' => $nxtPage->id]);
			Session::put('page_id', $nxtPage->id);

			// finish page
			$page->finish();

			// return to next page
			return $nxtPage->generate();
		}
		elseif(!is_null($pid))
		{
			// TBD manual page
		}
	}

	/**
	 *	create an automatic response.
	 *
	 *	@return Response
	 */
	public function respond()
	{
		#\Log::debug(Session::get('page_id'));

		// if finished
		if(is_null($this))
			return redirect('/');

		// check if test limit(s) are reached
		if($this->reachedLimits(true))
		{
			// update database
			if($this->isAuthGuarded())
			{
				$this->users()->updateExistingPivot($this->user->id, ['finished' => true]);
			}

			// clear session
			Session::flush();
			Session::regenerate();

			// redirect to home
			return redirect('/');
		}

		// JSON vs. Blade view
		if(Request::wantsJson())
		{
			// stay on page or leave
			if($this->reachedLimits())
			{
				// get page
				$page = $this->pages()->with(['items' => function ($query) {
					$query->orderBy('position');
				}])->findOrFail($this->user->pivot->page_id);

				// get next page
				$nxtPage = $page->next()->first();

				// if no next Page is available
				if(is_null($nxtPage))
					return redirect('/');

				return response()->json(['redirect' => true, 'token' => csrf_token(), /*'assets' => $nxtPage->assets()*/]);
			}
			else
				return response()->json($this->generate());
		}
		else
			return $this->generate()->toView();
	}

	/**
	 *	validate a request.
	 *
	 *	@return object
	 */
	public function reachedLimits($onlyTestLimits = false)
	{
		// abort of no object is given
		if(is_null($this))
			return null;

		// check test time limit
		if(($this->time_limit->getTimestamp() > 0) AND ((Carbon::now()->getTimestamp() - Session::get('start_time_global')->getTimestamp()) >= $this->time_limit->getTimestamp()))
			return true;

		// check page limits if allowed
		if(!$onlyTestLimits)
		{
			// get current page
			$page = $this->pages()->current();

			// check page time limits
			if((isset($page->time_limit)) AND ($page->time_limit > 0) AND ((Carbon::now()->getTimestamp() - Session::get('start_time_page')->getTimestamp()) >= $page->time_limit))
				return true;

			// check page call limits
			if(($page->repetitions == 0) OR ($page->repetitions < Session::get('page_visit_counter')))
				return true;
		}

		// return false if every check is passed
		return false;
	}

	/**
	 *	start a page.
	 *
	 *	@return object
	 */
	public function start($template = 'comproso::site')
	{
		// get assets
		$assets = json_decode($this->assets);

		// get first page
		$page = $this->pages()->orderBy('position')->with(['items' => function ($query) {
			$query->orderBy('position');
		}])->firstOrFail();

		// add assets
		$assets = array_merge($assets, json_decode($page->assets));

		// return view
		return View::make($template, ['assets' => $assets])->render();
	}

	/**
	 *	validate a request.
	 *
	 *	@return object
	 */
	public function validate()
	{
		// TBD
		return true;
	}

	/**
	 *	get a specific testing type.
	 *
	 *	@return result
	 */
	public function scopeOfType($query, $type)
	{
		if(!in_array($type, ['test', 'project']))
			\Log::error('invalid test type.');

		return $query->where('type', $type);
	}

	/**
	 *	get active tests.
	 *
	 *	@return result
	 */
	public function scopeIsActive($query)
	{
		return $query->where('active', true);
	}
}
