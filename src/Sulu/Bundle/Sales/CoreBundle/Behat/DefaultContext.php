<?php

namespace Sulu\Bundle\Sales\CoreBundle\Behat;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Sulu\Bundle\AdminBundle\Behat\AdminContext;

/**
 * Behat default context class for SuluSales.
 */
class DefaultContext extends AdminContext
{
    /**
     * @Given I add product :product to demand item table
     *
     * @param string $product
     *
     * @throws ElementNotFoundException
     */
    public function addProductToHuskyItemTable($product)
    {
        $itemTableFormElement = $this->getSession()->getPage()->find('css', '#item-table-form');

        if (null === $itemTableFormElement) {
            throw new ElementNotFoundException($this->getSession(), '#item-table-form');
        }

        // Press add button in table.
        $addButtonElement = $itemTableFormElement->find('css', '.toolbar-item.add-row');

        if (null === $addButtonElement) {
            throw new ElementNotFoundException($this->getSession(), '#item-table-form .toolbar-item.add-row');
        }

        $addButtonElement->click();

        // Now wait for the new table row.
        $this->waitForSelector('#item-table-form .item-table-row.new');

        // Fill in auto complete product.
        $this->iSelectFromTheHuskyAutoComplete($product, '.item-table-row.new .product-search');

        // Wait until loading is finished.
        $this->spin(function (RawMinkContext $context) {
            $spinnerElement = $context->getSession()->getPage()->find(
                'css',
                '.item-table-row.new .product-search .spinner'
            );

            if ($spinnerElement && $spinnerElement->isVisible()) {
                return false;
            }

            return true;
        });
    }

    /**
     * @Given I wait for :value seconds
     */
    public function waitForSeconds($value)
    {
        sleep($value);
    }
}
