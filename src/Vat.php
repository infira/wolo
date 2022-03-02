<?php

namespace Wolo;

class Vat
{
	private static int|float $percent = 20;
	
	public static function setPercent(float $percent)
	{
		self::$percent = $percent;
	}
	
	/**
	 * Get vat percent calculation nr
	 *
	 * @param int|float|null $vatPercent
	 * @return int|float
	 */
	private static function getCalcPercent(int|float $vatPercent = null): int|float
	{
		$vatP = $vatPercent == null ? self::$percent : $vatPercent;
		
		return ($vatP / 100) + 1;
	}
	
	/**
	 * Get vat amount
	 *
	 * @param int|float      $amount            - amount without vat
	 * @param bool           $amountContainsVat - $amountalready contasins vat
	 * @param int|float|null $vatPercent        - percent of vat, if null then default is used
	 * @return int|float
	 */
	public static function get(int|float $amount, bool $amountContainsVat, int|float|null $vatPercent = null): int|float
	{
		$amount = floatval($amount);
		if ($amountContainsVat == true) {
			$output = $amount - ($amount / self::getCalcPercent($vatPercent));
		}
		else {
			$output = ($amount * self::getCalcPercent($vatPercent)) - $amount;
		}
		
		return $output;
	}
	
	/**
	 * Add vat to amount
	 *
	 * @param int|float      $net        - amount without vat
	 * @param int|float|null $vatPercent - percent of vat, if null then default is used
	 * @return int|float
	 */
	public static function add(int|float $net, int|float|null $vatPercent = null): int|float
	{
		return $net + self::get($net, false, $vatPercent);
	}
	
	/**
	 * Remove vat from amount
	 *
	 * @param int|float      $gross      - amount with vat
	 * @param int|float|null $vatPercent - percent of vat, if null then default is used
	 * @return int|float
	 */
	public static function remove(int|float $gross, int|float|null $vatPercent = null): int|float
	{
		return $gross - self::get($gross, true, $vatPercent);
	}
}