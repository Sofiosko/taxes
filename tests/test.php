<?php
require_once __DIR__ . '/../src/taxes.php';

/**
 * Whole calculation logic
 * -------------------------------------------
 */
$cl = new \BiteIT\Taxes\CalcLogic();


/**
 * Data
 * -------------------------------------------
 */
$pl = new \BiteIT\Taxes\PriceList(\BiteIT\Taxes\Rates::HIGH_PERCENT, $cl);

$pl->addWithVat(121, 1, null, 20);
$pl->addWithVat(146.7, 2.5, null, 21);

$pl->addWithVat(200, 2, \BiteIT\Taxes\Rates::MEDIUM_PERCENT, 22);
$pl->addWithVat(223.3, 3, \BiteIT\Taxes\Rates::MEDIUM_PERCENT, 23);

$pl->addWithVat(299, 4, \BiteIT\Taxes\Rates::LOW_PERCENT, 24);
$pl->addWithVat(305.2, 4.5, \BiteIT\Taxes\Rates::LOW_PERCENT, 25);

var_dump($pl->getPriceById(20));
// adds item with negative price (discount for specific vat rate)
//$pl->addWithVat(-100, 1, \BiteIT\Taxes\Rates::HIGH_PERCENT);
// sets discount for all products proportionately
//$pl->setDiscount(100, true);


/**
 * Example recap
 * -------------------------------------------
 */
$view = new \BiteIT\Taxes\View\TotalListRecap($pl);
$view->render();

echo '<br />';
echo '<br />';
echo '<br />';
echo '<hr />';
echo '<pre>';
var_dump($pl);