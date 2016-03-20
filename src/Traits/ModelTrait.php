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

trait ModelTrait
{
	// define Models
	protected $ItemModel		= 'Comproso\Framework\Models\Item';
	protected $PageModel		= 'Comproso\Framework\Models\Page';
	protected $ResultModel		= 'Comproso\Framework\Models\Result';
	protected $TestModel		= 'Comproso\Framework\Models\Test';
	protected $UserModel		= 'App\User';
}