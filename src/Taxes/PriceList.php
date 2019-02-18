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

    /** @var array */
    protected $pricesMap = [];

    /** @var int */
    protected $iCounter = 0;

    /** @var Discount */
    protected $discount = null;

    protected $totalsWithVatBeforeDiscount = [];

    protected $totalsWithoutVatBeforeDiscount = [];

    public function __construct($defaultVatPercent, ICalcLogic $compLogic = null)
    {
        $this->defaultVatPercent = $defaultVatPercent;
        $this->calcLogic = isset($compLogic) ? $compLogic : new CalcLogic();
    }

    /**
     * @param $priceWithVat
     * @param float $quantity
     * @param null $vatPercent
     * @param null $priceId
     * @return Price
     */
    public function addWithVat($priceWithVat, $quantity = 1.0, $vatPercent = null, $priceId = null)
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        $this->prices[] = $price = Price::createFromPriceWithVat($this->calcLogic, $vatPercent, $priceWithVat, $quantity);
        if(isset($priceId))
            $this->pricesMap[$priceId] = $price;
        return $price;
    }

    /**
     * @param $priceWithoutVat
     * @param float $quantity
     * @param null $vatPercent
     * @param null $priceId
     * @return Price
     */
    public function addWithoutVat($priceWithoutVat, $quantity = 1.0, $vatPercent = null, $priceId = null)
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        $this->prices[] = $price = Price::createFromPriceWithoutVat($this->calcLogic, $vatPercent, $priceWithoutVat, $quantity);
        if(isset($priceId))
            $this->pricesMap[$priceId] = $price;
        return $price;
    }

    /**
     * @param $priceId
     * @return mixed|null
     */
    public function getPriceById($priceId){
        if(isset($this->pricesMap[$priceId]))
            return $this->pricesMap[$priceId];
        return null;
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

    public function getTotalWithVatWithoutDiscount()
    {
        return array_sum($this->totalsWithVatBeforeDiscount);
    }

    public function getTotalWithoutVatWithoutDiscount()
    {
        return array_sum($this->totalsWithoutVatBeforeDiscount);
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

    /**
     * @param $amount
     * @param bool $isOnVat
     * @return $this
     */
    public function setDiscount($amount, $isOnVat = true)
    {
        $this->discount = new Discount($amount, $isOnVat);
        $priceListTotal = $isOnVat ? $this->getTotalWithVat() : $this->getTotalWithoutVat();
        if ($this->discount->getAmount() > $priceListTotal) {
            throw new \InvalidArgumentException('Discount cannot be higher than total price');
        }

        $this->totalsWithVatBeforeDiscount = $this->getTotalsWithVat();
        $this->totalsWithoutVatBeforeDiscount = $this->getTotalsWithoutVat();

        /**
         * Get amount of discount for every vat group
         */
        $totalDiscounts = [];
        $totals = $isOnVat ? $this->getTotalsWithVat() : $this->getTotalsWithoutVat();
        foreach ($totals as $vatPercent => $total) {
            $ratioToTotal = round((($total * 100) / $priceListTotal) / 100, 4);
            $totalDiscounts[$vatPercent] = ($amount * $ratioToTotal);
        }

        $checkSum = array_sum($totalDiscounts);
        if ($checkSum < $amount)
            $totalDiscounts[array_keys($totalDiscounts)[0]] += ($amount - $checkSum);

        foreach ($this->prices as $price) {
            /**
             * Calculate amount of discount for unit price proportionaly to total in specific group
             */
            $totalPrice = $isOnVat ? $price->getTotalPriceWithVat() : $price->getTotalPriceWithoutVat();
            $total = $totals[$price->getVatPercent()];
            $unitRatioToTotal = round((($totalPrice * 100) / $total) / 100, 2);

            $discount = round(($totalDiscounts[$price->getVatPercent()] * $unitRatioToTotal) / $price->getQuantity(), 2);

            $price->setDiscount($discount, $isOnVat);
        }

        return $this;
    }

    /**
     * @return Discount|null
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return bool
     */
    public function hasDiscount()
    {
        return $this->discount instanceof Discount;
    }

    public function clear()
    {
        $this->prices = [];
        $this->discount = null;
        $this->iCounter = 0;
        return $this;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
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

    /**
     * @param mixed $offset
     * @return Price|mixed|null
     */
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