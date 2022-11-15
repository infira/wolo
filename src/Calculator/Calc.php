<?php

namespace Wolo\Calculator;


use InvalidArgumentException;

class Calc
{
    /**
     * @example Calc::increaseByPercent(100,50) //150
     */
    public static function increaseByPercent(float|int $amount, float|int $percent): float|int
    {
        if ($percent <= 0) {
            throw new InvalidArgumentException('cant increase with negative');
        }

        return $amount + static::percentageAmount($amount, $percent);
    }

    /**
     * @example Calc::decreaseByPercent(100,50) //50
     */
    public static function decreaseByPercent(float|int $amount, float|int $percent): float|int
    {
        if ($percent <= 0) {
            throw new InvalidArgumentException('cant decrease with negative');
        }

        return $amount - static::percentageAmount($amount, $percent);
    }

    /**
     * @example Calc::percentageAmount(100,50) //50
     */
    public static function percentageAmount(float|int $amount, float|int $percent): float|int
    {
        return $amount * ($percent / 100);
    }
}