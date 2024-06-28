<?php

namespace BiteIT\Taxes;

class PriceList implements \ArrayAccess, \Iterator
{
    /** @var float */
    protected float $defaultVatPercent;

    protected ICalcLogic|CalcLogic $calcLogic;

    /** @var Price[] */
    protected array $prices = [];

    /** @var array */
    protected array $pricesMap = [];

    /** @var int */
    protected int $iCounter = 0;

    protected ?Discount $discount = null;

    protected array $totalsWithVatBeforeDiscount = [];

    protected array $totalsWithoutVatBeforeDiscount = [];

    public function __construct($defaultVatPercent, ICalcLogic $compLogic = null)
    {
        $this->defaultVatPercent = $defaultVatPercent;
        $this->calcLogic = $compLogic ?? new CalcLogic();
    }

    /**
     * @param $priceWithVat
     * @param float $quantity
     * @param null $vatPercent
     * @param null $priceId
     * @return Price
     */
    public function addWithVat($priceWithVat, float $quantity = 1.0, $vatPercent = null, $priceId = null): Price
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        $this->prices[] = $price = Price::createFromPriceWithVat($this->calcLogic, $vatPercent, $priceWithVat, $quantity);
        if (isset($priceId))
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
    public function addWithoutVat($priceWithoutVat, float $quantity = 1.0, $vatPercent = null, $priceId = null): Price
    {
        if (!isset($vatPercent))
            $vatPercent = $this->defaultVatPercent;
        $this->prices[] = $price = Price::createFromPriceWithoutVat($this->calcLogic, $vatPercent, $priceWithoutVat, $quantity);
        if (isset($priceId))
            $this->pricesMap[$priceId] = $price;
        return $price;
    }

    /**
     * @param $priceId
     * @return Price|null
     */
    public function getPriceById($priceId): ?Price
    {
        if (isset($this->pricesMap[$priceId]))
            return $this->pricesMap[$priceId];
        return null;
    }

    /**
     * @return array
     */
    public function getTotalsWithVat(): array
    {
        return $this->calcLogic->getTotalsWithVatFromPrices($this->prices);
    }

    /**
     * @return array
     */
    public function getTotalsWithoutVat(): array
    {
        return $this->calcLogic->getTotalsWithoutVatFromPrices($this->prices);
    }

    /**
     * @return float
     */
    public function getTotalWithVat(): float
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
    public function getTotalWithoutVat(): float
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
    public function getTotalVat(): float
    {
        return $this->getTotalWithVat() - $this->getTotalWithoutVat();
    }

    public function getTotalWithVatRounded(int $precision = 0): float
    {
        return round($this->getTotalWithVat(), $precision);
    }

    public function getRounding(int $precision = 0): float
    {
        return round($this->getTotalWithVatRounded($precision) - $this->getTotalWithVat(), 4);
    }

    public function getTotalWithVatWithoutDiscount(): float|int
    {
        return array_sum($this->totalsWithVatBeforeDiscount);
    }

    public function getTotalWithoutVatWithoutDiscount(): float|int
    {
        return array_sum($this->totalsWithoutVatBeforeDiscount);
    }

    /**
     * @return array
     */
    public function getVatBases(): array
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
    public function getVatPercents(): array
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
    public function setDiscount(float|int $amount, bool $isOnVat = true): static
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

    public function addDiscountItem(float $amount, int|float $vatPercent, int $discountId, bool $isWithVat = true): static
    {
        if($isWithVat)
            $this->addWithVat(-$amount, 1, $vatPercent, $discountId);
        else
            $this->addWithoutVat(-$amount, 1, $vatPercent, $discountId);
        return $this;
    }

    /**
     * @return Discount|null
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->discount instanceof Discount;
    }

    public function clear(): static
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
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->prices[] = $value;
        } else {
            $this->prices[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->prices[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->prices[$offset]);
    }

    /**
     * @param mixed $offset
     * @return Price|mixed|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->prices[$offset] ?? null;
    }

    public function rewind(): void
    {
        reset($this->prices);
    }

    public function current(): mixed
    {
        $var = current($this->prices);
        return $var;
    }

    public function key(): mixed
    {
        $var = key($this->prices);
        return $var;
    }

    public function next(): void
    {
        next($this->prices);
    }

    public function valid(): bool
    {
        $key = key($this->prices);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}
