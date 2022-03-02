<?php

namespace Wolo;

use Wolo\Traits\NumberTrait;

class Num
{
	/**
	 * Trait is used cause then u can use NumberTrait in other Num.php files
	 * For example in Laravel make your own Num class
	 * namespace App\Support;
	 * class Num extends \Else\Awesome\Class
	 * {
	 *      use \Wolo\Traits\NumberTrait;
	 *
	 *      ... your own methods
	 * }
	 *
	 * also, traits are easily overridable
	 */
	use NumberTrait;
}