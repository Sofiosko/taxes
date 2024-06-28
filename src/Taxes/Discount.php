<?php

namespace BiteIT\Taxes;

/**
 * Discount is different then item in price list it only contains info about amount and if it affects directly price with vat or without vat
 *
 * If you want to set discount as item in price list you can add item with negative amount
 *
 * Class Discount
 * @package BiteIT\Taxes
 */
class Discount
{
    /** @var float */
    public float $amount = 0.0;

    /** @var bool  */
    public mixed $isOnVat = true;

//    /** @var null|int  */
//    public $vatPercent = null;

    public function __construct($amount, $isOnVat = true)
    {
        $this->amount = $amount;
        $this->isOnVat = $isOnVat;
    }

    public function getAmount($precision = 2): float
    {
        return round($this->amount, $precision);
    }
}
