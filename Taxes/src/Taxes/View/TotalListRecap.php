<?php

namespace BiteIT\Taxes\View;

use BiteIT\Taxes\PriceList;

class TotalListRecap
{
    /** @var ListRecap */
    protected $listRecap;

    /** @var Totals */
    protected $totals;

    public function __construct(PriceList $list)
    {
        $this->listRecap = new ListRecap($list);
        $this->totals = new Totals($list);
    }

    public function render()
    {
        $this->listRecap->render();
        $this->totals->render();
    }
}