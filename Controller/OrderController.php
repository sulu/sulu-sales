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
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\MissingOrderAttributeException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderDependencyNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\MissingArgumentException;
use Sulu\Component\Rest\Exception\RestException;
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

    protected static $entityKey = 'orders';

    /**
     * @return OrderManager
     */
    private function getManager()
    {
        return $this->get('sulu_sales_order.order_manager');
    }

    /**
     * returns all fields that can be used by list
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fieldsAction(Request $request)
    {
        $locale = $this->getLocale($request);
        // default contacts list
        return $this->handleView($this->view(array_values($this->getManager()->getFieldDescriptors($locale)), 200));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cgetAction(Request $request) {
        $filter = array();

        $locale = $this->getLocale($request);

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

            $restHelper->initializeListBuilder($listBuilder, $this->getManager()->getFieldDescriptors($locale));

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
     * Retrieves and shows an order with the given ID
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id order ID
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, $id)
    {
        $locale = $this->getLocale($request);
        $view = $this->responseGetById(
            $id,
            function ($id) use ($locale) {
                /** @var Order $order */
                $order = $this->getManager()->findByIdAndLocale($id, $locale);

                return $order;
            }
        );

        return $this->handleView($view);
    }

    /**
     * Creates and stores a new order.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        try {
            $order = $this->getManager()->save(
                $request->request->all(),
                $this->getLocale($request),
                $this->getUser()->getId()
            );

            $view = $this->view($order, 200);
        } catch (OrderDependencyNotFoundException $exc) {
            $exception = new EntityNotFoundException($exc->getEntityName(), $exc->getId());
            $view = $this->view($exception->toArray(), 400);
        } catch (MissingOrderAttributeException $exc) {
            $exception = new MissingArgumentException(self::$entityName, $exc->getAttribute());
            $view = $this->view($exception->toArray(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Change a order.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id order ID
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            $order = $this->getManager()->save(
                $request->request->all(),
                $this->getLocale($request),
                $this->getUser()->getId(),
                $id
            );

            $view = $this->view($order, 200);
        } catch (OrderNotFoundException $exc) {
            $exception = new EntityNotFoundException($exc->getEntityName(), $exc->getId());
            $view = $this->view($exception->toArray(), 404);
        } catch (OrderDependencyNotFoundException $exc) {
            $exception = new EntityNotFoundException($exc->getEntityName(), $exc->getId());
            $view = $this->view($exception->toArray(), 400);
        } catch (MissingOrderAttributeException $exc) {
            $exception = new MissingArgumentException(self::$entityName, $exc->getAttribute());
            $view = $this->view($exception->toArray(), 400);
        } catch (OrderException $exc) {
            $exception = new RestException($exc->getMessage());
            $view = $this->view($exception->toArray(), 400);
        }

        return $this->handleView($view);
    }

    public function confirmAction() {

    }

    /**
     * Delete an order with the given id.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id orderid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $id)
    {
        $locale = $this->getLocale($request);

        $delete = function ($id) use ($locale) {
            $this->getManager()->delete($id, $this->getUser()->getId());
        };
        $view = $this->responseDelete($id, $delete);

        return $this->handleView($view);
    }

}
