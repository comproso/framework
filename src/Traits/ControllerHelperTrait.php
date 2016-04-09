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

use Auth;

trait ControllerHelperTrait
{
	// define Auth Guard
	protected $authGuard = 'web';

	public function __construct()
	{
		$this->user = Auth::guard($this->authGuard)->user();
	}

	// list available projects
	public function index()
	{
		return $this->user->tests()->all();
	}

	// start or continue a test
	public function initialize($pid)
	{
		if($this->user->tests()->instance($pid)->start())
			return true;
		else
			return false;
	}


}