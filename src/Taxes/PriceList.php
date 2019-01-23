<?php

namespace BiteIT\Taxes;

class PriceList implements \ArrayAccess, \Iterator
{
    /** @var float */
    protected $defaultVatPercent;

    /** @var ICalcLogic */
    protected $calcLogic;

    /** @var Price[] */
    protected $prices = [];

    /** @var int */
    protected $iCounter = 0;

    /** @var float */
    protected $discount = 0.0;

    public function __construct($defaultVatPercent, ICalcLogic $compLogic = null)
    {
        $this->defaultVatPercent = $defaultVatPercent;
        $this->calcLogic = isset($compLogic) ? $compLogic : new CalcLogic();
    }

    /**
     * @param $priceWithVat
     * @param float $quantity
     * @param null $vatPercent
     * @return Price
     */
    public function addWithVat($priceWithVat, $quantity = 1.0, $vatPercent = null)
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        return $this->prices[] = Price::createFromPriceWithVat($this->calcLogic, $vatPercent, $priceWithVat, $quantity);
    }

    /**
     * @param $priceWithoutVat
     * @param float $quantity
     * @param null $vatPercent
     * @return Price
     */
    public function addWithoutVat($priceWithoutVat, $quantity = 1.0, $vatPercent = null)
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        return $this->prices[] = Price::createFromPriceWithoutVat($this->calcLogic, $vatPercent, $priceWithoutVat, $quantity);
    }

    /**
     * @return array
     */
    public function getTotalsWithVat()
    {
        return $this->calcLogic->getTotalsWithVatFromPrices($this->prices);
    }

    /**
     * @return array
     */
    public function getTotalsWithoutVat()
    {
        return $this->calcLogic->getTotalsWithoutVatFromPrices($this->prices);
    }

    /**
     * @return float
     */
    public function getTotalWithVat()
    {
        $total = 0.0;
        foreach ($this->getTotalsWithVat() as $price) {
            $total += $price;
        }
        return $total;
    }

    /**
     * @return float
     */
    public function getTotalWithoutVat()
    {
        $total = 0.0;
        foreach ($this->getTotalsWithoutVat() as $price) {
            $total += $price;
        }
        return $total;
    }

    /**
     * @return float
     */
    public function getTotalVat()
    {
        return $this->getTotalWithVat() - $this->getTotalWithoutVat();
    }

    public function getTotalWithVatRounded($precision = 0)
    {
        return round($this->getTotalWithVat(), $precision);
    }

    public function getRounding($precision = 0)
    {
        return round($this->getTotalWithVatRounded($precision) - $this->getTotalWithVat(), 4);
    }

    /**
     * @return array
     */
    public function getVatBases()
    {
        $bases = [];
        foreach ($this->getTotalsWithVat() as $vatPercent => $price) {
            $bases[$vatPercent] = $price - $this->getTotalsWithoutVat()[$vatPercent];
        }
        return $bases;
    }

    /**
     * @return array
     */
    public function getVatPercents()
    {
        $percents = [];
        foreach ($this->prices as $price) {
            if (!in_array($price->getVatPercent(), $percents))
                $percents[] = $price->getVatPercent();
        }
        return $percents;
    }

    public function setDiscount($amount)
    {
        $this->discount = $amount;
        return $this;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->prices[] = $value;
        } else {
            $this->prices[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->prices[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->prices[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->prices[$offset]) ? $this->prices[$offset] : null;
    }

    public function rewind()
    {
        reset($this->prices);
    }

    public function current()
    {
        $var = current($this->prices);
        return $var;
    }

    public function key()
    {
        $var = key($this->prices);
        return $var;
    }

    public function next()
    {
        $var = next($this->prices);
        return $var;
    }

    public function valid()
    {
        $key = key($this->prices);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}