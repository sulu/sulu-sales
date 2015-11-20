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
use Symfony\Component\HttpFoundation\Request;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\ItemPriceCalculator;
use Sulu\Component\Rest\RestController;

/**
 * Handles price calculations by api.
 */
class PricingController extends RestController implements ClassResourceInterface
{
    /**
     * Calculate pricing of an array of items
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        try {
            $data = $request->request->all();

            $locale = $this->getLocale($request);

            $prices = $this->calculateItemPrices($data['items'], $data['currency'], $data['taxfree'], $locale);

            $view = $this->view($prices['items'], 200);

        } catch (OrderDependencyNotFoundException $exc) {
            $exception = new EntityNotFoundException($exc->getEntityName(), $exc->getId());
            $view = $this->view($exception->toArray(), 400);
        } catch (MissingOrderAttributeException $exc) {
            $exception = new MissingArgumentException(self::$orderEntityName, $exc->getAttribute());
            $view = $this->view($exception->toArray(), 400);
        }

        return $this->handleView($view);
    }

    private function calculateItemPrices($itemsData, $currency, $taxfree, $locale)
    {
        $calculator = $this->getItemPriceCalculator();
        $totalPrice = 0;
        $items = [];

        foreach ($itemsData as $itemData) {
            $useProductsPrice = false;
            if (isset($itemData['useProductsPrice']) && $itemData['useProductsPrice'] == true) {
                $useProductsPrice = $itemData['useProductsPrice'];
            }

            $item = $this->getItemManager()->save($itemData, $locale);
            $itemPrice = $calculator->getItemPrice($item, $currency, $useProductsPrice);
            $itemTotalPrice = $calculator->calculate($item, $currency, $useProductsPrice);
            $item->setPrice($itemPrice);
            $item->setTotalNetPrice($itemTotalPrice);

            $items[] = $item;
            $totalPrice += $itemPrice;
        }

        return [
            'total' => $totalPrice,
            'items' => $items,
        ];
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
