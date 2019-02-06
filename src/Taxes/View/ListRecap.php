<?php

namespace BiteIT\Taxes\View;

use BiteIT\Taxes\Price;
use BiteIT\Taxes\PriceList;

class ListRecap
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
                <th>#</th>
                <th>Price with VAT</th>
                <th>Quantity</th>
                <th>Total with VAT</th>
                <th>VAT in %</th>
                <th>Price without VAT</th>
                <th>Total without VAT</th>
            </tr>
            </thead>
            <tbody>
            <?php
            /**
             * @var Price $price
             */
            foreach ($this->list as $i => $price) {
                $unitPriceWithVat = $price->getUnitPriceWithVat();
                $unitPriceWithoutVat = $price->getUnitPriceWithoutVat();

                $originalWithVat = $price->getOriginalPriceWithVat();
                $originalWithoutVat = $price->getOriginalPriceWithoutVat();
                ?>
                <tr>
                    <td><?php echo($i + 1); ?></td>
                    <td>
                        <?php echo ($unitPriceWithVat != $originalWithVat) ? '<span style="text-decoration: line-through">'.$originalWithVat.'</span><br />' : ''  ?>
                        <?php echo $unitPriceWithVat; ?>
                    </td>
                    <td><?php echo $price->getQuantity(); ?></td>
                    <td><?php echo $price->getTotalPriceWithVat(); ?></td>
                    <td><?php echo $price->getVatPercent(); ?></td>
                    <td>
                        <?php echo ($unitPriceWithoutVat != $originalWithoutVat) ? '<span style="text-decoration: line-through">'.$originalWithoutVat.'</span><br />' : ''  ?>
                        <?php echo $unitPriceWithoutVat; ?>
                    </td>
                    <td><?php echo $price->getTotalPriceWithoutVat(); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
        echo ob_get_clean();
    }
}