<?php

namespace Comproso\Framework\Helpers;

use Comproso\Framework\Traits\ModelTrait;
use Comproso\Framework\Traits\ControllerHelperTrait;

/**
 *	Controller Helper.
 *
 *	Easily integrating provided traits.
 *
 *	@copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 *	@license AGPL-3.0.
 *
 *	@deprecated 0.7		This class is deprecated.
 *
 */
class ControllerHelper extends Controller
{
	use Modeltrait, ControllerHelperTrait;
}
