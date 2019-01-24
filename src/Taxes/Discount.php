<?php

namespace BiteIT\Taxes;

use http\Exception\InvalidArgumentException;

class Discount
{
    /** @var float */
    public $amount = 0.0;

    /** @var null|int  */
    public $vatPercent = null;

    public function __construct($amount, $vatPercent = null)
    {
        $this->amount = $amount;
        $this->vatPercent = $vatPercent;
    }

    public function getAmountWithVat(){
        return $this->amount;
    }
}