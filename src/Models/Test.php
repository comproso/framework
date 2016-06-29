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

			$item->name = (empty(trim($row->name))) ? null : trim($row->name);

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
	public function export($params = [])
	{
		// abort if no results
		if($this->results()->count() == 0)
			return null;

		// define params
		$includeIncompleteResults = (isset($params['incomplete'])) ? boolval($params['incomplete']) : true;
		$deleteUsedResultsAfterExport = (isset($params['delete'])) ? boolval($params['delete']) : false;
		$extension = (isset($params['extension'])) ? $params['extension'] : 'xlsx';
		$addition = (isset($params['add'])) ? '_'.mt_rand(100000, 999999) : "";

		// get raw items
		//$rawItems = $this->items()->get(['items.id', 'items.name']);

		// prepare items
		/*$items = [];
		foreach($rawItems as $item)
			$items[$item->id] = ($item->name !== null) ? $item->name : 'i'.$item->id;*/

		// get test users and results
		$users = $this->users()->with(['results' => function ($query) {
			$query->orderBy('test_repetition_counter')
				->orderBy('page_repetition_counter')
				->orderBy('id');
		}])->get(['users.id', 'users.identifier']);

		// get pages
		$pages = $this->pages()->orderBy('position')->with(['items' => function ($query) {
			$query->orderBy('items.position')/*->addSelect([ 'items.id', 'items.name'])*/;
		}])->get(['pages.id', 'pages.repetitions']);

		// prepare columns
		$columns['user'] = null;
		$items = [];

		// prepare items
		foreach($pages as $page)
		{
			foreach($page->items as $item)
				$items[$item->id] = ($item->name !== null) ? $item->name : 'i'.$item->id;
		}

		// create columns
		for($i = 0; $i < $this->repetitions; $i++)
		{
			foreach($pages as $page)
			{
				for($j = 0; $j <= $page->repetitions; $j++)
				{
					$columns['t'.$i]['p'.$page->id]['r'.$page->repetitions] = [
						'server_time' => null,
						'user_time' => null,
						'process_data' => null
					];

					foreach($page->items as $item)
						$columns['t'.$i]['p'.$page->id]['r'.$page->repetitions][$items[$item->id]] = null;
				}
			}
		}

		// prepare vars
		//$testRepetitions = $this->repetitions;

		// prepare export
		$export = Excel::create(date("Y-m-d")."_".urlencode(str_replace(" ", "_", $this->name)).$addition, function ($excel) use ($users, $items, $columns) {
			// general infos

			// create sheet
			$excel->sheet('Results', function ($sheet) use ($users, $items, $columns) {

				// create user results
				foreach($users as $user)
				{
					// continue if no results
					if((!isset($user->results)) OR (count($user->results) === 0))
						continue;

					// prepare results
					$results = $columns;

					$results['user'] = $user->identifier;

					// get results
					foreach($user->results as $result)
					{
						$results['t'.$result->test_repetition_counter]['p'.$result->page_id]['r'.$result->page_repetition_counter] = [
							'server_time' => $result->server_time_delta,
							'user_time' => $result->user_time_delta,
							'process_data' => $result->process_data
						];

						// add single results
						foreach(json_decode($result->values) as $itemId => $value)
						{
							$results['t'.$result->test_repetition_counter]['p'.$result->page_id]['r'.$result->page_repetition_counter][$items[$itemId]] = $value;
						}
					}

					// create row
					$rows[] = array_dot($results);
				}

				// fill sheet
				$sheet->fromArray($rows, null, 'A1', true);
			});
		});

		// return result
		return $export;
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
			Session::put('start_time_page', microtime(true));
			#Session::put('current_page', $page->id);
			Session::put('page_visit_counter', 0);

			return $page;
		}

		// prepare session
		// TBD
	}

	/**
	 *	Generate a testing page.
	 *
	 *	@return object
	 */
	public function generate($pid = null)
	{
		// interrupt if no data available
		if(is_null($this))
			return null;

		// check if finished
		if($this->user->pivot->finished)
			return redirect('/');

		// initialize page
		if(!Session::has('page_id'))
		{
			if($this->isAuthGuarded())
				return $this->guarded()->initialize()->generate();
			else
				return $this->initialize()->generate();

			\Log::debug('ja');
		}

		// display page
		if(is_int($pid))
			return $this->pages()->orderBy('position')->with(['items' => function ($query) {
					$query->orderBy('position');
				}])->findOrFail($pid)->generate();
		else
			return $this->pages()->orderBy('position')->with(['items' => function ($query) {
					$query->orderBy('position');
				}])->findOrFail(Session::get('page_id'))->generate();
	}

	/**
	 *	Proceed a test page.
	 *
	 *	@return object
	 */
	public function proceed($pid = null)
	{
		// abort if HTML request
		if(!Request::wantsJson())
			return null;

		// validate request
		if(!$this->validate())
			return null;

		// abort if limits are reached
		#if((!$this->reachedLimits(true)) AND ($this->reachedLimits()))
		#	return $this;

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
		}

		// get current page
		$page = $this->pages()->with(['items' => function ($query) {
			$query->orderBy('position');
		}])->findOrFail($this->user->pivot->page_id);


		// proceed page
		$proceed = $page->proceed();

		// check for finish
		if($this->limits())
			return $this->finish();

		// check user input
		if((boolval(Request::input('cctrl_prvs')) === true) AND ($page->returnable))	// go back
		{
			// get previous page
			$prvsPage = $page->previous()->first();

			// choose page
			if((!is_null($prvsPage)) AND ($prvsPage->returnable))
				$nextPage = $prvsPage;
			else
				$nextPage = $page->next()->first();
		}
		elseif($page->limits())		// limits reached
			$nextPage = $page->next()->first();
		else
			$nextPage = $page;

		// abort of no page available
		if((is_null($nextPage)) OR ($page->finish))
			return $this->finish();

		// prepare response
		$this->prepareResponse($nextPage);
	}

	// limit detection
	public function limits()
	{
		// time limit
		if(($this->time_limit->getTimestamp() > 0) AND ($this->time_limit->getTimestamp() <= Carbon::now()->diffInSeconds(Session::get('start_time_global'))))
			return true;

		// false if everything is passed
		return false;
	}

	// prepare response
	public function prepareResponse($page)
	{
		// check if next page exists
		if(is_null($page))
			return $this->finish();

		// get data
		$pageId = Session::pull('page_id');
		$visits = Session::pull('page_visit_counter');

		// check if current page is recalled
		if($pageId == $page->id)
			$visits++;
		else
		{
			$visits = 0;

			Session::put('HtmlResponse', true);
		}

		// update DB
		if($this->isAuthGuarded())
			$this->users()->updateExistingPivot($this->user->id, ['page_id' => $page->id, 'page_repetitions' => $visits]);

		// update session
		Session::put('page_id', $page->id);
		Session::put('page_visit_counter', $visits);
	}

	// finish test
	public function finish()
	{
		// update DB
		if($this->isAuthGuarded())
			$this->users()->updateExistingPivot($this->user->id, [
				'page_id' => null,
				'repetitions' => ++$this->user->pivot->repetitions,
				'finished' => ($this->repetitions <= $this->user->pivot->repetitions) ? true : false,
			]);

		// update Session
		Session::flush();
		Session::regenerate();
	}

	/**
	 *	create an automatic response.
	 *
	 *	@return Response
	 */
	public function respond()
	{
		// if finished
		if(is_null($this))
			return redirect('/');

		// limits reached
		if($this->limits())
			return $this->finish();

		// check if is endet
		if(($this->isAuthGuarded()) AND ($this->repetitions <= $this->user->pivot->repetitions))
			return redirect('/');

		// JSON vs. Blade view
		if(!Request::wantsJson())
			return $this->generate()->toView();
		elseif((!Session::has('HtmlResponse')) OR (Session::get('HtmlResponse')))
		{
			Session::put('HtmlResponse', false);

			return response()->json(['redirect' => true, 'token' => csrf_token()]);
		}
		else
			return response()->json($this->generate());
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
