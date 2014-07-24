<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Component\Manager\AbstractManager;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;

class OrderManager extends AbstractManager
{
    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Describes the fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    public function __construct(
        OrderRepository $orderRepository,
        ObjectManager $em
    )
    {
        $this->orderRepository = $orderRepository;
        $this->em = $em;

        $this->initializeFieldDescriptors();
    }

    public function save(array $data, $locale, $userId, $id = null)
    {
        if ($id) {
            $order = $this->orderRepository->findByIdAndLocale($id, $locale);

            if (!$order) {
                throw new OrderNotFoundException($id);
            }
        } else {
            $order = new Order(new OrderEntity(), $locale);
        }

    }

    public function delete()
    {

    }

    /**
     * get all field descriptors
     * @return \Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor[]
     */
    public function getFieldDescriptors()
    {
        return $this->fieldDescriptors;
    }

    /**
     * returns a specific field descriptor by key
     * @param $key
     * @return DoctrineFieldDescriptor
     */
    public function getFieldDescriptor($key)
    {
        return $this->fieldDescriptors[$key];
    }

    /**
     * Finds an order by id and locale
     * @param $id
     * @param $locale
     * @return null|Product
     */
    public function findByIdAndLocale($id, $locale)
    {
        $product = $this->productRepository->findByIdAndLocale($id, $locale);

        if ($product) {
            return new Product($product, $locale);
        } else {
            return null;
        }
    }

    /**
     * initializes field descriptors
     */
    private function initializeFieldDescriptors()
    {
        $this->fieldDescriptors['id'] = new DoctrineFieldDescriptor('id', 'id', self::$orderEntityName);
    }
}
