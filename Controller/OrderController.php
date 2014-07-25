<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Hateoas\Representation\CollectionRepresentation;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Makes orders available through a REST API
 * @package Sulu\Bundle\Sales\OrderBundle\Controller
 */
class OrderController extends RestController implements ClassResourceInterface
{

    protected static $entityName = 'SuluSalesOrderBundle:Order';

    /**
     * Returns the repository object for AdvancedProduct
     *
     * @return OrderManager
     */
    private function getManager()
    {
        return $this->get('sulu_sales_order.order_manager');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cgetAction(Request $request) {
        $filter = array();

        $status = $request->get('status');
        if ($status) {
            $filter['status'] = $status;
        }

        if ($request->get('flat') == 'true') {
            /** @var RestHelperInterface $restHelper */
            $restHelper = $this->get('sulu_core.doctrine_rest_helper');

            /** @var DoctrineListBuilderFactory $factory */
            $factory = $this->get('sulu_core.doctrine_list_builder_factory');

            $listBuilder = $factory->create(self::$entityName);

            $restHelper->initializeListBuilder($listBuilder, $this->getManager()->getFieldDescriptors());

            foreach ($filter as $key => $value) {
                $listBuilder->where($this->getManager()->getFieldDescriptor($key), $value);
            }

            $list = new ListRepresentation(
                $listBuilder->execute(),
                self::$entityKey,
                'get_orders',
                $request->query->all(),
                $listBuilder->getCurrentPage(),
                $listBuilder->getLimit(),
                $listBuilder->count()
            );
        } else {
            $list = new CollectionRepresentation(
                $this->getManager()->findAllByLocale($this->getLocale($request), $filter),
                self::$entityKey
            );
        }

        $view = $this->view($list, 200);
        return $this->handleView($view);
    }

    /**
     * Retrieves and shows a product with the given ID
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id product ID
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, $id)
    {
        $locale = $this->getLocale($request);
        $view = $this->responseGetById(
            $id,
            function ($id) use ($locale) {
                /** @var Product $product */
                $product = $this->getManager()->findByIdAndLocale($id, $locale);

                return $product;
            }
        );

        return $this->handleView($view);
    }

}
