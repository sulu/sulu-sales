<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\ItemPriceCalculator;
use Symfony\Component\HttpFoundation\Request;
use Sulu\Component\Rest\RestController;

/**
 * Handles price calculations by api.
 */
class PricingController extends RestController implements ClassResourceInterface
{
    public function postAction(Request $request)
    {
        try {
            $data = $request->request->all();

            $this->getItemPrices($data['items'], $data['items']);

            $view = $this->view($data, 200);

        } catch (OrderDependencyNotFoundException $exc) {
            $exception = new EntityNotFoundException($exc->getEntityName(), $exc->getId());
            $view = $this->view($exception->toArray(), 400);
        } catch (MissingOrderAttributeException $exc) {
            $exception = new MissingArgumentException(self::$orderEntityName, $exc->getAttribute());
            $view = $this->view($exception->toArray(), 400);
        }

        return $this->handleView($view);
    }

    private function calculateItemPrices($itemsData, $currency, $taxfree)
    {
        $calculator = $this->getItemPriceCalculator();
        $prices = 0;

        foreach ($itemsData as $itemData) {
            $item = $this->getItemManager()->save($itemData);
            $itemPrice = $calculator->calculate($item);
            $prices += $itemPrice;
        }

    }

    /**
     * @return ItemPriceCalculator
     */
    private function getItemPriceCalculator()
    {
        return $this->get('sulu_sales_core.item_price_calculator');
    }

    /**
     * @return ItemManager
     */
    private function getItemManager()
    {
        return $this->get('sulu_sales_core.item_manager');
    }
}
