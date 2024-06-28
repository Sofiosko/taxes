<?php

namespace BiteIT\Taxes;

/**
 * Class CalcLogic
 * @package BiteIT\Taxes
 */
interface ICalcLogic
{
    /**
     * Method for calculating price with vat from price without vat
     *
     * @param Price $price
     * @return float|int
     */
    public function getUnitPriceWithVatFromPriceObject(Price $price): float|int;

    /**
     * Method for calculating price without vat from price with vat
     *
     * @param Price $price
     * @return float
     */
    public function getUnitPriceWithoutVatFromPriceObject(Price $price): float;

    /**
     * Method for calculating total amount with vat from price object
     *
     * @param Price $price
     * @return float|int
     */
    public function getTotalPriceWithVatFromPriceObject(Price $price): float|int;

    /**
     * Method for calculating total amount without vat from price object
     *
     * @param Price $price
     * @return float|int
     */
    public function getTotalPriceWithoutVatFromPriceObject(Price $price): float|int;

    /**
     * Method for calculating array of totals with vat from prices array
     *
     * @param Price[] $prices
     * @return array
     */
    public function getTotalsWithVatFromPrices(array $prices): array;

    /**
     * Method for calculating array of totals without vat from prices array
     *
     * @param Price[] $prices
     * @return mixed
     */
    public function getTotalsWithoutVatFromPrices(array $prices): mixed;

    /**
     * Method for calculating amout of vat
     *
     * @param Price $price
     * @return mixed
     */
    public function getTotalVatFromPriceObject(Price $price): mixed;

    /**
     * Returns correctly rounded var coefficient
     *
     * @param $vatPercent
     * @return float
     */
    public function getVatCoefficient(float|int $vatPercent): float;

    /**
     * Returns false if passed percentage is not allowed
     *
     * @param $vatPercent
     * @return mixed
     */
    public function validateVatPercent(float|int $vatPercent): mixed;
}
