<?php

namespace BiteIT\Taxes;

use http\Exception\InvalidArgumentException;

class Price
{
    /** @var float */
    public $priceWithVat;

    /** @var float */
    public $priceWithoutVat;

    /** @var int */
    public $vatPercent;

    /** @var float */
    public $quantity = 1.0;

    /** @var float */
    public $discount = 0.0;

    /** @var ICalcLogic */
    protected $calcLogic;

    /**
     * Price constructor.
     * @param ICalcLogic $compLogic
     * @param $vatPercent
     * @param null $priceWithVat
     * @param null $priceWithoutVat
     * @param float $quantity
     */
    public function __construct(ICalcLogic $compLogic, $vatPercent, $priceWithVat = null, $priceWithoutVat = null, $quantity = 1.0)
    {
        if (!isset($priceWithVat) && !isset($priceWithoutVat))
            throw new InvalidArgumentException('Please specify at least one price');

        $this->priceWithoutVat = $priceWithoutVat;
        $this->priceWithVat = $priceWithVat;
        $this->vatPercent = $vatPercent;
        $this->quantity = $quantity;
        $this->calcLogic = $compLogic;
    }

    /**
     * @param ICalcLogic $compLogic
     * @param $vatPercent
     * @param $priceWithVat
     * @param float $quantity
     * @return Price
     */
    public static function createFromPriceWithVat(ICalcLogic $compLogic, $vatPercent, $priceWithVat, $quantity = 1.0)
    {
        return new static($compLogic, $vatPercent, $priceWithVat, null, $quantity);
    }

    /**
     * @param ICalcLogic $compLogic
     * @param $vatPercent
     * @param $priceWithoutVat
     * @param float $quantity
     * @return Price
     */
    public static function createFromPriceWithoutVat(ICalcLogic $compLogic, $vatPercent, $priceWithoutVat, $quantity = 1.0)
    {
        return new static($compLogic, $vatPercent, null, $priceWithoutVat, $quantity);
    }

    /**
     * @return float
     */
    public function getTotalPriceWithVat()
    {
        return $this->calcLogic->getTotalPriceWithVatFromPriceObject($this);
    }

    /**
     * @return float
     */
    public function getTotalPriceWithoutVat()
    {
        return $this->calcLogic->getTotalPriceWithoutVatFromPriceObject($this);
    }

    /**
     * @return float
     */
    public function getUnitPriceWithVat()
    {
        if (!isset($this->priceWithVat)) {
            $this->priceWithVat = $this->calcLogic->getUnitPriceWithVatFromPriceObject($this);
        }
        return $this->priceWithVat;
    }

    /**
     * @return float
     */
    public function getUnitPriceWithoutVat()
    {
        if (!isset($this->priceWithoutVat)) {
            $this->priceWithoutVat = $this->calcLogic->getUnitPriceWithoutVatFromPriceObject($this);
        }
        return $this->priceWithoutVat;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @return float
     */
    public function getVatCoefficient()
    {
        return $this->calcLogic->getVatCoefficient($this->getVatPercent());
    }

    /**
     * @return float
     */
    public function getVatRatio()
    {
        return static::calculateVatRatio($this->getVatPercent());
    }

    /**
     * @param $vatPercent
     * @return float
     */
    public static function calculateVatRatio($vatPercent)
    {
        return round((100 + $vatPercent) / 100, 4);
    }

    public function setDiscount($amount)
    {
        $this->discount = $amount;
        return $this;
    }
}