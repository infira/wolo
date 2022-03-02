<?php

namespace Wolo;

use Wolo\Traits\StringTrait;

class Str
{
	/**
	 * Trait is used cause then u can use StringTrait in other Str.php files
	 * For example in Laravel make your own Str class
	 * namespace App\Support;
	 * class Str extends \Illuminate\Support\Str
	 * {
	 *      use \Wolo\Traits\StringTrait;
	 *
	 *      ... your own methods
	 * }
	 *
	 * also, traits are easily overridable
	 */
	use StringTrait;
}