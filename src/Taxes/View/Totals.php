<?php

namespace BiteIT\Taxes\View;

use BiteIT\Taxes\PriceList;

class Totals
{
    /** @var PriceList */
    protected $list;

    public function __construct(PriceList $list)
    {
        $this->list = $list;
    }

    public function render()
    {
        ob_start();
        ?>
        <table>
            <thead>
            <tr>
                <th colspan="4"><h3>Totals</h3></th>
            </tr>
            <tr>
                <th></th>
                <th>DPH</th>
                <th>Totals without VAT</th>
                <th>Totals with VAT</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->list->getVatBases() as $vatPercent => $vatBase) {
                ?>
                <tr>
                    <td><?php echo $vatPercent ?>%</td>
                    <td><?php echo $vatBase ?></td>
                    <td><?php echo $this->list->getTotalsWithoutVat()[$vatPercent] ?></td>
                    <td><?php echo $this->list->getTotalsWithVat()[$vatPercent] ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>
            <?php
            if($this->list->hasDiscount()){
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td><strong>Before discount:</strong></td>
                    <td><?php echo $this->list->getTotalWithVatWithoutDiscount() ?></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td><strong>Discount:</strong></td>
                    <td><?php echo $this->list->getDiscount()->getAmount() ?></td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td></td>
                <td><strong>Total VAT</strong></td>
                <td><strong>Total without VAT</strong></td>
                <td><strong>Total with VAT</strong></td>
            </tr>
            <tr>
                <td></td>
                <td><?php echo $this->list->getTotalVat() ?></td>
                <td><?php echo $this->list->getTotalWithoutVat() ?></td>
                <td><?php echo $this->list->getTotalWithVat() ?></td>
            </tr>
            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><strong>Rounding:</strong></td>
                <td><?php echo $this->list->getRounding() ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><strong>To pay:</strong></td>
                <td><?php echo $this->list->getTotalWithVatRounded() ?></td>
            </tr>
            </tbody>
        </table>
        <?php
        echo ob_get_clean();
    }
}