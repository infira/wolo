<?php

namespace Wolo\Calculator;

class Vat
{
    public static float|int $percent = 20;

    public static function setPercent(float $percent): void
    {
        self::$percent = $percent;
    }

    /**
     * Get vat percent calculation nr
     * @param  float|int|null  $vatPercent
     * @return float|int
     */
    private static function getCalcPercent(float|int $vatPercent = null): float|int
    {
        $vatP = $vatPercent ?: self::$percent;

        return ($vatP / 100) + 1;
    }

    /**
     * Get vat amount
     * @param  float|int  $amount
     * @param  bool  $amountContainsVat
     * @param  float|int|null  $vatPercent  - percent of vat, if null then default is used
     * @return float|int
     */
    public static function get(float|int $amount, bool $amountContainsVat, float|int|null $vatPercent = null): float|int
    {
        $amount = (float)$amount;
        if ($amountContainsVat) {
            $output = $amount - ($amount / self::getCalcPercent($vatPercent));
        }
        else {
            $output = ($amount * self::getCalcPercent($vatPercent)) - $amount;
        }

        return $output;
    }

    /**
     * Add vat to amount
     * @param  float|int  $net  - amount without vat
     * @param  float|int|null  $vatPercent  - percent of vat, if null then default is used
     * @return float|int
     */
    public static function add(float|int $net, float|int|null $vatPercent = null): float|int
    {
        return $net + self::get($net, false, $vatPercent);
    }

    /**
     * Remove vat from amount
     * @param  float|int  $gross  - amount with vat
     * @param  float|int|null  $vatPercent  - percent of vat, if null then default is used
     * @return float|int
     */
    public static function remove(float|int $gross, float|int|null $vatPercent = null): float|int
    {
        //return Calc::decreaseByPercent($gross,$vatPercent ?: self::$percent);
        return $gross - self::get($gross, true, $vatPercent);
    }
}