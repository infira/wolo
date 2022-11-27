<?php

namespace Wolo\Traits;

trait NumberTrait
{
    /**
     * Generate reference number for banks
     *
     * @param  int  $of
     * @return int
     * @link https://www.pangaliit.ee/settlements-and-standards/reference-number-of-the-invoice
     */
    public static function referenceNumber(int $of): int
    {
        $svn = (string)$of;
        $weights = [7, 3, 1];
        $count = 0;
        $sum = 0;
        for ($i = strlen($svn) - 1; $i >= 0; $i--) {
            $sum += $weights[$count % 3] * $svn[$i];
            $count++;
        }
        $check = (10 - ($sum % 10)) % 10;

        return (int)"$of$check";
    }

    /**
     * Convert numeric string to float|int
     * @param  mixed  $value
     * @return float|int
     * @example numeric('100,000,45') //100000.45
     * @example numeric('100,000.45') //100000.45
     */
    public static function numeric(float|int|string|bool|null $value): float|int
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if ($value === false || $value === null) {
            return 0;
        }
        if ($value === true) {
            return 1;
        }
        $tmp = trim($value);
        $tmp = str_replace(' ', '', $tmp);//assume tht space is a thousand separator

        //usually US uses "," to separate thousands and "." as decimal point or other way around
        //then we can assume last separator is decimal separator
        $tmp = str_replace(",", ".", $tmp);
        $ex = explode('.', $tmp);
        $c = count($ex);
        if ($c === 1) {
            return (int)$ex[0];
        }
        $tmp = implode('', array_slice($ex, 0, -1)).'.'.$ex[array_key_last($ex)];

        if (str_contains($tmp, ".")) {
            return (float)str_replace(",", ".", $tmp);
        }

        return (int)$tmp;
    }

    /**
     * Returns numeric string with . as decimal separator
     * @param  mixed  $value
     * @return string
     * @example numeric('100,000,45') //"100000.45"
     * @example numeric('100,000.45') //"100000.45"
     */
    public static function numericString(mixed $value): string
    {
        return (string)self::numeric($value);
    }

    /**
     * Format a number with grouped thousands
     * @link https://php.net/manual/en/function.number-format.php
     * @param  mixed  $value
     * @param  string  $decimalSeparator
     * @param  string  $thousand
     * @return string
     */
    public static function formatNumber(mixed $value, string $decimalSeparator = ',', string $thousand = ''): string
    {
        return number_format(
            self::numeric($value),
            2,
            $decimalSeparator,
            $thousand
        );
    }

    /**
     * generate document no from ID
     * e.g. ID of docNo(1234,6) becomes 001234
     *
     * @param  int  $documentID
     * @param  int  $length
     * @return string
     */
    public static function docNo(int $documentID, int $length = 6): string
    {
        $short = max(0, $length - strlen($documentID));

        return mb_substr(str_repeat('0', $short), 0, $short).$documentID;
    }
}