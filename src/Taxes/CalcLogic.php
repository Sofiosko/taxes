<?php

namespace BiteIT\Taxes;

/**
 * TODO prevent cyclical dependence? Or change price object parameters to specific parameters?
 *
 * Class CalcLogic
 * @package BiteIT\Taxes
 */
class CalcLogic implements ICalcLogic
{
    /**
     * Method for calculating price with vat from price without vat
     *
     * @param Price $price
     * @return float|int
     */
    public function getUnitPriceWithVatFromPriceObject(Price $price): float|int
    {
        return $price->getUnitPriceWithoutVat() * $price->getVatRatio();
    }

    /**
     * Method for calculating price without vat from price with vat
     *
     * @param Price $price
     * @return float
     */
    public function getUnitPriceWithoutVatFromPriceObject(Price $price): float
    {
        return $price->getUnitPriceWithVat() - round($price->getUnitPriceWithVat() * $price->getVatCoefficient(), 2);
    }

    /**
     * Method for calculating total amount with vat from price object
     * NOTE: May switch with commented line in getTotalsWithVatFromPrices for different approach
     *
     * @param Price $price
     * @return float|int
     */
    public function getTotalPriceWithVatFromPriceObject(Price $price): float|int
    {
        return $price->getUnitPriceWithVat() * $price->getQuantity();
//        return round($price->getUnitPriceWithoutVat() * $price->quantity * $price->getVatRatio(), 2);
    }

    /**
     * Method for calculating total amount without vat from price object
     *
     * @param Price $price
     * @return float|int
     */
    public function getTotalPriceWithoutVatFromPriceObject(Price $price): float|int
    {
        return round($price->getUnitPriceWithoutVat() * $price->quantity, 2);
    }

    /**
     * Method for calculating array of totals with vat from prices array
     * NOTE: May switch with commented line in getTotalPriceWithVatFromPriceObject for different approach
     *
     * @param Price[] $prices
     * @return array
     */
    public function getTotalsWithVatFromPrices($prices): array
    {
        $totalsWithVat = [];
        foreach ($this->getTotalsWithoutVatFromPrices($prices) as $vatPercent => $totalWithoutVat) {
            $totalsWithVat[$vatPercent] = round($totalWithoutVat * Price::calculateVatRatio($vatPercent), 2);
        }
        return $totalsWithVat;

        // Can be used to calculate from original total
//        $totalsWithVat = [];
//        foreach($prices as $price){
//            if(!isset($totalsWithVat[$price->getVatPercent()]))
//                $totalsWithVat[$price->getVatPercent()] = 0;
//            $totalsWithVat[$price->getVatPercent()] += $price->getTotalPriceWithVat();
//        }
//        return $totalsWithVat;
    }

    /**
     * Method for calculating array of totals without vat from prices array
     *
     * @param Price[] $prices
     * @return mixed
     */
    public function getTotalsWithoutVatFromPrices($prices): mixed
    {
        $totals = [];
        foreach ($prices as $price) {
            if (!isset($totals[$price->getVatPercent()]))
                $totals[$price->getVatPercent()] = 0;
            $totals[$price->getVatPercent()] += $price->getTotalPriceWithoutVat();
        }
        return $totals;
    }

    public function getTotalVatFromPriceObject(Price $price): mixed
    {
        return round($price->getTotalPriceWithVat() - $price->getTotalPriceWithoutVat(), 2);
    }

    /**
     * Returns correctly rounded var coefficient
     *
     * @param $vatPercent
     * @return float
     */
    public function getVatCoefficient($vatPercent): float
    {
        return round($vatPercent / (100 + $vatPercent), 4);
    }

    /**
     * @param $vatPercent
     * @return bool|mixed
     */
    public function validateVatPercent($vatPercent): mixed
    {
        return in_array($vatPercent, [
            Rates::LOW_PERCENT,
            Rates::MEDIUM_PERCENT,
            Rates::HIGH_PERCENT
        ]);
    }
}
