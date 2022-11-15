<?php

namespace Wolo\Calculator;


class Pricing
{
    /** Calc discount percent by discount amount*/
    public static function discount(float $price, float|int $percent): float|int
    {
        return Calc::decreaseByPercent($price, $percent);
    }

    /** Calc discount percent by discount amount*/
    public static function markup(float $price, float|int $percent): float|int
    {
        return Calc::increaseByPercent($price, $percent);
    }

}