<?php

namespace Wolo;

use Wolo\Traits\StrTrait;

class Str
{
	/**
	 * Trait is used cause then u can use StrTrait in other Str.php files
	 * For example in Laravel make your own Str class
	 * namespace App\Support;
	 * class Str extends \Illuminate\Support\Str
	 * {
	 *      use \Wolo\Traits\StrTrait;
	 *
	 *      ... your own methods
	 * }
	 */
	use StrTrait;
}