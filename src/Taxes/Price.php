<?php

namespace BiteIT\Taxes;

class Price
{
    /** @var float */
    public ?float $priceWithVat;

    /** @var float */
    public ?float $priceWithoutVat;

    /** @var int */
    public int $vatPercent;

    /** @var float */
    public float $quantity = 1.0;

    /** @var Discount|null */
    public ?Discount $discount = null;

    /** @var ICalcLogic */
    protected ICalcLogic $calcLogic;

    /** @var float */
    protected ?float $originalPriceWithVat;

    /** @var float */
    protected ?float $originalPriceWithoutVat;

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
            throw new \InvalidArgumentException('Please specify at least one price');

        if(!$compLogic->validateVatPercent($vatPercent))
            throw new \InvalidArgumentException($vatPercent.' is not allowed vat rate');

        $this->priceWithoutVat = $priceWithoutVat;
        $this->priceWithVat = $priceWithVat;
        $this->vatPercent = $vatPercent;
        $this->quantity = $quantity;
        $this->calcLogic = $compLogic;

        $this->originalPriceWithoutVat = $priceWithoutVat;
        $this->originalPriceWithVat = $priceWithVat;
    }

    /**
     * @param ICalcLogic $compLogic
     * @param $vatPercent
     * @param $priceWithVat
     * @param float $quantity
     * @return Price
     */
    public static function createFromPriceWithVat(ICalcLogic $compLogic, $vatPercent, $priceWithVat, $quantity = 1.0): Price
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
    public static function createFromPriceWithoutVat(ICalcLogic $compLogic, $vatPercent, $priceWithoutVat, $quantity = 1.0): Price
    {
        return new static($compLogic, $vatPercent, null, $priceWithoutVat, $quantity);
    }

    /**
     * @return float
     */
    public function getTotalPriceWithVat(): float
    {
        return $this->calcLogic->getTotalPriceWithVatFromPriceObject($this);
    }

    /**
     * @return float
     */
    public function getTotalPriceWithoutVat(): float
    {
        return $this->calcLogic->getTotalPriceWithoutVatFromPriceObject($this);
    }

    public function getTotalVat(){
        return $this->calcLogic->getTotalVatFromPriceObject($this);
    }

    /**
     * @return float
     */
    public function getUnitPriceWithVat(): ?float
    {
        if (!isset($this->priceWithVat)) {
            $this->priceWithVat = $this->calcLogic->getUnitPriceWithVatFromPriceObject($this);
            if(!isset($this->originalPriceWithVat))
                $this->originalPriceWithVat = $this->priceWithVat;
        }
        return $this->priceWithVat;
    }

    /**
     * @return float
     */
    public function getUnitPriceWithoutVat(): ?float
    {
        if (!isset($this->priceWithoutVat)) {
            $this->priceWithoutVat = $this->calcLogic->getUnitPriceWithoutVatFromPriceObject($this);
            if(!isset($this->originalPriceWithoutVat))
                $this->originalPriceWithoutVat = $this->priceWithoutVat;
        }
        return $this->priceWithoutVat;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getVatPercent(): int
    {
        return $this->vatPercent;
    }

    /**
     * @return float
     */
    public function getVatCoefficient(): float
    {
        return $this->calcLogic->getVatCoefficient($this->getVatPercent());
    }

    /**
     * @return float
     */
    public function getVatRatio(): float
    {
        return static::calculateVatRatio($this->getVatPercent());
    }

    /**
     * @param $vatPercent
     * @return float
     */
    public static function calculateVatRatio($vatPercent): float
    {
        return round((100 + $vatPercent) / 100, 4);
    }

    /**
     * @param $amount
     * @param bool $isOnVat
     * @return $this
     */
    public function setDiscount($amount, $isOnVat = true): static
    {
        if($isOnVat){
            $discountedPriceWithVat = $this->getUnitPriceWithVat() - $amount;
            $this->priceWithVat = $discountedPriceWithVat;
            $this->priceWithoutVat = null;
        } else {
            $discountedPriceWithoutVat = $this->getUnitPriceWithoutVat() - $amount;
            $this->priceWithoutVat = $discountedPriceWithoutVat;
            $this->priceWithVat = null;
        }
        return $this;
    }

    public function getOriginalPriceWithVat(): ?float
    {
        return $this->originalPriceWithVat;
    }

    public function getOriginalPriceWithoutVat(): ?float
    {
        return $this->originalPriceWithoutVat;
    }
}
