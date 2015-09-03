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
    public function findByOrderId($id, $locale = null)
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
     * @param $includeStatusDeliveryNote defines if status deliveryNote should be included
     * @return array|null
     */
    public function getSumOfShippedItemsByOrderId($orderId, $includeStatusDeliveryNote)
    {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->select('partial shipping.{id}, partial items.{id}, sum(shippingItems.quantity) AS shipped')
                ->leftJoin('shipping.order', 'o', 'WITH', 'o.id = :orderId')
                ->join('o.items', 'items')
                ->join('shipping.status', 'status')
                ->leftJoin('shipping.shippingItems', 'shippingItems', 'WITH', 'items = shippingItems.item')
                ->setParameter('orderId', $orderId)
                ->groupBy('items.id')
                ->where('status.id = :statusId')
                ->setParameter('statusId', ShippingStatus::STATUS_SHIPPED);
            if ($includeStatusDeliveryNote) {
                $qb->orWhere('status.id = :statusId2')
                    ->setParameter('statusId2', ShippingStatus::STATUS_DELIVERY_NOTE);
            }
            return $qb->getQuery()->getScalarResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }

    /**
     * returns all items that were shipped by itemId
     * @param $itemId
     * @param $includeStatusDeliveryNote defines if status deliveryNote should be included
     * @return int
     */
    public function getSumOfShippedItemsByItemId($itemId, $includeStatusDeliveryNote)
    {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->select('sum(shippingItems.quantity) AS shipped')
                ->join('shipping.shippingItems', 'shippingItems')
                ->join('shippingItems.item', 'item', 'WITH', 'item.id = :itemId')
                ->join('shipping.status', 'status')
                ->groupBy('item.id')
                ->where('status.id = :statusId')
                ->setParameter('itemId', $itemId)
                ->setParameter('statusId', ShippingStatus::STATUS_SHIPPED);
            if ($includeStatusDeliveryNote) {
                $qb->orWhere('status.id = :statusId2')
                    ->setParameter('statusId2', ShippingStatus::STATUS_DELIVERY_NOTE);
            }
            return $qb->getQuery()->getSingleScalarResult();
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
                ->join('shipping.order', 'o')
                ->join('shipping.status', 'status')
                ->where('o.id = :orderId')
                ->groupBy('o.id')
                ->setParameter('orderId', $orderId);
            foreach ($statusIds as $statusId) {
                $qb->andWhere('status.id ' . $comparator . ' :excludeStatus');
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
    private function getShippingQuery($locale = null)
    {
        $qb = $this->createQueryBuilder('shipping')
            ->leftJoin('shipping.deliveryAddress', 'deliveryAddress')
            ->leftJoin('shipping.status', 'status')
            ->leftJoin('shipping.shippingItems', 'shippingItems')
            ->leftJoin('shippingItems.item', 'items');

        if ($locale) {
            $qb->leftJoin('status.translations', 'statusTranslations', 'WITH', 'statusTranslations.locale = :locale')
                ->setParameter('locale', $locale);
        }
        return $qb;
    }
}
