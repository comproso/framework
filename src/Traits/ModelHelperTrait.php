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

namespace Comproso\Framework\Traits;

use Illuminate\Http\Request;

use Auth;

trait ModelHelperTrait
{
	protected $guard = 'web';


	public function __construct()
	{
		#$this->request = new Request;
	}

	// get instance of a model
	public function instance($model, $altClass = null, $fail = false)
	{
		// get current class/model
		if(is_null($altClass))
			$class = get_class();
		else
			$class = (string) $class;

		// get instance
		if(is_a($model, $class))
			return $model;
		elseif(is_int($model))
			return ($fail) ? $this->findOrFail($model) : $this->find($model);

		return null;
	}

	/**
	 *	Test guarding.
	 *
	 *	@return
	 */
	public function guarded($guard = null)
	{
		if(!$this->isAuthGuarded())
		{
			if(is_null($guard))
				$guard = $this->guard;

			$this->user = $this->users()->findOrFail(Auth::guard($guard)->user()->id);
		}

		return $this;
	}

	/**
	 *	Test guarding.
	 *
	 *	@return
	 */
	protected function isAuthGuarded()
	{
		return isset($this->user);
	}
}