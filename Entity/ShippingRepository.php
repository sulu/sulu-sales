<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * ShippingRepository
 *
 */
class ShippingRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Shipping|null
     */
    public function findById($id)
    {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->andWhere('shipping.id = :shippingId')
                ->setParameter('shippingId', $id);

            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * Returns all shippings in the given locale
     * @param string $locale The locale of the shipping to load
     * @return Shipping[]|null
     */
    public function findAllByLocale($locale)
    {
        try {
            return $this->getShippingQuery($locale)->getQuery()->getResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * Returns all shippings and filters them
     * @param $locale
     * @param array $filter
     * @return Shipping[]|null
     */
    public function findByLocaleAndFilter($locale, array $filter)
    {
        try {
            $qb = $this->getShippingQuery($locale);

            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'status':
                        $qb->andWhere('status.id = :' . $key);
                        $qb->setParameter($key, $value);
                        break;
                    case 'orderId':
                        $qb->leftJoin('shipping.order', 'o');
                        $qb->andWhere('o.id = :' . $key);
                        $qb->setParameter($key, $value);
                        break;
                }
            }

            $query = $qb->getQuery();
            return $query->getResult();
        } catch (NoResultException $ex) {
            return null;
        }
    }

    /**
     * Finds a shipping by id and locale
     * @param $id
     * @param $locale
     * @return Shipping|null
     */
    public function findByIdAndLocale($id, $locale)
    {
        try {
            $qb = $this->getShippingQuery($locale);
            $qb->andWhere('shipping.id = :shippingId');
            $qb->setParameter('shippingId', $id);

            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * Finds a shipping by a order id
     * @param $id
     * @param $locale
     * @return array|null
     */
    public function findByOrderId($id, $locale)
    {
        try {
            $qb = $this->getShippingQuery($locale);
            $qb->andWhere('shipping.order = :id');
            $qb->setParameter('id', $id);

            return $qb->getQuery()->getResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * returns sum of items that are assigned to a shipping which is not in status 'created'
     * @param $orderId
     * @return array|null
     */
    public function getSumOfShippedItemsByOrderId($orderId)
    {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->select('partial shipping.{id}, partial items.{id}, sum(shippingItems.quantity) AS shipped')
                ->leftJoin('shipping.order', 'o', 'WITH', 'o.id = :orderId')
                ->join('o.items', 'items')
                ->leftJoin('shipping.shippingItems', 'shippingItems', 'WITH', 'items = shippingItems.item')
                ->setParameter('orderId', $orderId)
                ->groupBy('items.id')
                ->where('shipping.status != :excludeStatus')
                ->setParameter('excludeStatus', ShippingStatus::STATUS_CREATED);
            return $qb->getQuery()->getScalarResult();

            // TODO: implement sql to resolve group by problem in postgres
//            $dql = 'SELECT sum(shippingItems.quantity) AS sumShipped, item.id '.
//                'FROM ss_shippings shipping' .
//                'LEFT JOIN so_orders o ON shipping.idOrders = o.id AND (o.id = 1) ' .
//                'INNER JOIN so_order_items orderItems ON o.id = orderItems.idOrders ' .
//                'INNER JOIN sc_item item ON item.id = orderItems.idItems ' .
//                'LEFT JOIN ss_shipping_items shippingItems ON shipping.id = shippingItems.idShippings AND (item.id = shippingItems.idItems)' .
//                'WHERE shipping.idShippingStatus <> 1 ' .
//                'GROUP BY item.id';
//            $qb = $this->getEntityManager()->createQuery($dql);
//            return $qb->getScalarResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * returns number of shippings by order id
     * @param $orderId
     * @param array $statusIds
     * @param string $comparator
     * @return int|mixed
     */
    public function countByOrderId($orderId, $statusIds = array(), $comparator = "=")
    {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->select('count(shipping.id)')
                ->join('shipping.order','o')
                ->join('shipping.status','status')
                ->where('o.id = :orderId')
                ->groupBy('o.id')
                ->setParameter('orderId', $orderId);
            foreach ($statusIds as $statusId) {
                $qb->andWhere('status.id '.$comparator.' :excludeStatus');
                $qb->setParameter('excludeStatus', $statusId);
            }

            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $nre) {
            return 0;
        }
    }

    /**
     * Returns query for shippings
     * @param string $locale The locale to load
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getShippingQuery($locale)
    {
        $qb = $this->createQueryBuilder('shipping')
            ->leftJoin('shipping.deliveryAddress', 'deliveryAddress')
            ->leftJoin('shipping.status', 'status')
            ->leftJoin('status.translations', 'statusTranslations', 'WITH', 'statusTranslations.locale = :locale')
            ->leftJoin('shipping.shippingItems', 'shippingItems')
            ->leftJoin('shippingItems.item', 'items')
            ->setParameter('locale', $locale);
        return $qb;
    }
}
