<?php

namespace Wolo\Traits;

trait NumberTrait
{
	/**
	 * Generate reference number for banks
	 *
	 * @param int $of
	 * @see https://www.pangaliit.ee/settlements-and-standards/reference-number-of-the-invoice
	 * @return int
	 */
	public static function referenceNumber(int $of): int
	{
		$svn     = "$of";
		$weights = [7, 3, 1];
		$count   = 0;
		$sum     = 0;
		for ($i = strlen($svn) - 1; $i >= 0; $i--) {
			$sum += $weights[$count % 3] * $svn[$i];
			$count++;
		}
		$check = (10 - ($sum % 10)) % 10;
		
		return (int)"$of$check";
	}
}