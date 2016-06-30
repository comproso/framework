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
use Request;
use Session;
use Validator;
use View;

use Carbon\Carbon;

use Comproso\Framework\Traits\ModelTrait;
use Comproso\Framework\Traits\ModelHelperTrait;
use Comproso\Framework\Models\Result;

class Page extends Model
{
	use ModelTrait, ModelHelperTrait;

    // table
    protected $table = 'pages';

    // whitelist
    protected $fillable = ['results', 'template'];

    // JSON protection
    protected $hidden = ['template', 'nav', 'repetitions'];

    // timestamps
    #protected $dates = ['repetition_interval', 'time_limit'];

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

    // Result Model
    public function results()
    {
	    return $this->hasMany($this->ResultModel);
    }

    /*
	 *	special Page functionalities
	 *
	 */


	// generation
	public function generate()
	{
		\Log::debug($this->id."(".$this->position."):".Session::get('page_visit_counter').'/'.$this->repetitions);

		// prepare results
		$results = [];

		// get previous session
		if(Session::has('page_items'))
			$session = Session::get('page_items');
		else
			$session = null;

		foreach($this->getItems() as $item)
		{
			// get generation cache
			$cache = (isset($session[$item->id])) ? $session[$item->id] : null;

			// store generated Item
			$results[$item->id] = $item->generate($cache);
		}

		// prepare page and return Page object
		$page = new Page;

		// add standard assets
		if(!Request::wantsJson())
		{
			if(isset($this->assets))
				$assets = (is_array(json_decode($this->assets))) ? json_decode($this->assets) : [json_decode($this->assets)];
			else
				$assets = [];

			if($this->default_assets)
			{
				// debug vs production files
				/*if(getenv('APP_DEBUG') == true)*/
					array_push($assets, "vendor/comproso/framework/comproso.js");
				/*else
					array_push($assets, "vendor/comproso/framework/comproso.min.js");*/
			}

			$page->template = $this->template;
			$page->test_id = $this->test_id;
			$page->page_id = $this->id;
			$page->nav = $this->operations_template;
			$page->assets = $assets;
		}

		$page->time_limit = $this->time_limit;
		$page->interval = $this->repetition_interval;
		$page->results = $results;
		$page->repetitions = $this->repepetitions;

		// update session
		Session::put('start_time_page', microtime(true));

		return $page;
	}

	// proceeding of data
	public function proceed($reporting = null)
	{
		// get items
		if(isset($this->items))
			$items = $this->items;
		else
			$items = $this->items()->orderBy('position')->get();

		// get previous session
		if(Session::has('page_items'))
			$session = Session::get('page_items');
		else
			$session = null;

		// validate request
		// TBD

		// manage system and process data
		$sys_time = round(((microtime(true) - Session::get('start_time_page')) * 1000), 0);
		$usr_time = Request::input('ccusr_nd') - Request::input('ccusr_tstrt');
		$usr_actions = Request::input('ccusr_ctns');

		// prepare results
		$results = [];

		// validate and proceed items
		foreach($items as $item)
		{
			// skip if no proceeding needed
			if(!$item->proceed)
				continue;

			// get itemResult
			if(Request::has('item'.$item->id))
				$itemResult = $item->proceed(Request::input('item'.$item->id));
			elseif(isset($session[$item->id]))
				$itemResult = $item->proceed($session[$item->id]);
			else
				$itemResult = $item->proceed();

			// validate request data
			$validation = Validator::make(['item' => $itemResult], ['item' => $item->validation]);

			// store results if allowed
			if($validation->fails())
				\Log::error(get_class($item)." (".$item->id."): invalid request data");
			else	// cache item
				$results[$item->id] = $itemResult;
		}

		// store items temporarily in Session
		Session::put('page_items', $results);

		// check reporting config
		if(is_null($reporting))
			$reporting = $this->test()->first()->reporting;

		// report data
		if(($reporting) AND (Auth::check()))
		{
			// prepare process data
			$rawProcessData = json_decode(Request::input('ccusr_ctns'));
			$processData = [];

			// get User start time
			$usrStartTime = Request::input('ccusr_tstrt');

			// check if data were given
			if(!empty($rawProcessData))
			{
				foreach($rawProcessData as $usrAction)
				{
					// adjust timestamp
					$tmstmp = intval($usrAction->tstmp - $usrStartTime);
					#$tmstmp = intval(round(($tmstmp / 1000), 0));

					$processData[$tmstmp] = [
						intval(str_replace('item', '', $usrAction->item)) => $usrAction->value,	// !!!!!
					];
				}
			}

            // create (or update) Result model
            if(($this->returnable) OR ($this->recallable))
            {
	            $save = $this->results()->firstOrNew([
		            'user_id' => (int) Session::get('user_id'),
		            'test_repetition_counter' => intval(Session::get('test_repetition')),
		            'page_repetition_counter' => intval(Session::get('page_visit_counter'))
	            ]);
            }
            else
            {
	            $save = new Result;
	            $save->test_repetition_counter = intval(Session::get('test_repetition'));
	            $save->user_id = (int) Session::get('user_id');
	            $save->page_repetition_counter = intval(Session::get('page_visit_counter'));
            }

			// create Result model
            $save->values = json_encode($results);
            $save->process_data = json_encode($processData);
            $save->server_time_delta = intval(round(((microtime(true) - Session::get('start_time_page')) * 1000), 0));
            $save->user_time_delta = intval(Request::input('ccusr_nd') - $usrStartTime);

            // save result
            $this->results()->save($save);
		}

		return true;
	}

	/**
	 *	create the matching view.
	 *
	 *	@return object
	 */
	public function toView()
	{
		// abort if given
		if(is_null($this))
			return redirect('/');

		foreach($this->results as $result)
		{
			$results = $result->results;

			// add CSS
			if(!is_null($result->cssid))
				$results->cssid = $result->cssid;

			if(!is_null($result->cssclass))
				$results->cssclass = $result->cssclass;

			// add form name
			$results->name = $result->name;

			$views[] = View::make($result->template, $results)->render();
		}

		// add navigation bar
		if(!is_null($this->nav))
			$views[] = View::make($this->nav)->render();

		return View::make($this->template, [
			'test_id'		=> $this->test_id,
			'page_id'		=> $this->id,
			'time_limit'	=> $this->time_limit,
			'interval'		=> $this->interval,
			'results'		=> $views,
			'round'			=> intval(Session::get('page_visit_counter')),
			#'nav'			=> $this->nav,
			'assets'		=> $this->assets,
		])->render();
	}

	/**
	 *	create an automatic response.
	 *
	 *	@return Response
	 */
	public function respond()
	{
		// if null
		if(is_null($this))
			return redirect('/');

		// hint to test's function
		if(Auth::check())
			return $this->test()->findOrFail($this->test_id)->guarded()->respond();
		else
			return $this->test()->findOrFail($this->test_id)->respond();
	}

	/**
	 *	create valid assets.
	 *
	 *	@return Response
	 */
	public function assets()
	{
		if(is_null($this->assets))
			return [];

		// get assets
		$assets = json_decode($this->assets);

		// create path
		foreach($assets as $key => $asset)
		{
			$assets[$key] = asset($asset);
		}

		return $assets;
	}

	// limits
	public function limits()
	{
		if($this->repetitions <= Session::get('page_visit_counter'))
			return true;
		elseif(($this->time_limit > 0) AND ($this->time_limit < round((microtime(true) - Session::get('start_time_page')), 0)))
			return true;
		else
			return false;
	}

	/**
	 *	check if page or test limits are reached.
	 *
	 *	@return boolean
	 */
	/*public function reachedLimit()
	{
		// check testing time limit
		if(false)
			return true;

		// check page time limit
		if(false)
			return true;

		// check page call limit
		if(false)
			return true;

		// if no check matches, return false
		return false;
	}*/

	/**
	 *	finish a page.
	 *
	 *	@return
	 */
	public function finish()
	{
		// update current page
		/*Session::put('current_page', $this->next()->first()->id);
		Session::put('page_visit_counter', 0);

		// if user is authenticate
		if($this->isAuthGuarded())
		{

		}*/

		// update page visits for next page
		# --> move to initialzation in next version!

		// finish items
		foreach($this->getItems(false) as $item)
		{
			$item->finish();
		}

	}/*


	// export a page (headline)
	public function export()
	{
		// prepare cells
		$cells = ['server_time', 'user_time'];

		foreach($this->getItems() as $item)
		{
			if($item->export() !== null)
				$cells = array_merge($cells, $item->export());
		}

		// set position as ID
		$pageId = 'pos-'.$this->position;

		// add position prefix
		array_walk($cells, function (&$item) use ($pageId) {
			$item = $pageId."_".$item;
		});

		// return result
		return $cells;
	}
*/

	// get items
	public function getItems()
	{
		if(isset($this->items))
			return $this->items;
		else
			return $this->items()->orderBy('position')->get();
	}

	/**
	 *	page model at position X.
	 *
	 *	@return
	 */
	public function scopeOfPosition($query, $position = 1)
	{
		return $query->where('position', $position)->where('test_id', $this->test_id);
	}

	/**
	 *	current page model.
	 *
	 *	@return
	 */
	public function scopeCurrent($query)
	{
		if(Session::has('page_id'))
			return $query->find(Session::get('page_id'));
		else
			return $query->ofPosition()->first();
	}

	/**
	 *	following page model.
	 *
	 *	@return
	 */
	public function scopeNext($query)
	{
		return $query->ofPosition(($this->position + 1));
	}

	/**
	 *	previous page model.
	 *
	 *	@return
	 */
	public function scopePrevious($query)
	{
		return $query->ofPosition(($this->position - 1));
	}
}
