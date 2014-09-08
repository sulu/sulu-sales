<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
     * Finds a shipping by Order
     * @param $id
     * @param $locale
     * @return Shipping|null
     */
    public function findByOrder($id, $locale)
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

    public function getShippedItemsByOrderId($orderId) {
        try {
            $qb = $this->createQueryBuilder('shipping')
                ->select('partial shipping.{id}, partial o.{id}, partial items.{id}, partial shippingItems.{id}, sum(shippingItems.quantity) AS shipped')
                ->leftJoin('shipping.order', 'o', 'WITH', 'o.id = :orderId')
                ->join('o.items', 'items')
                ->leftJoin('shipping.shippingItems', 'shippingItems', 'WITH', 'items = shippingItems.item')
                ->setParameter('orderId', $orderId)
                ->groupBy('items.id')
                ->where('shipping.status != :excludeStatus')
                ->setParameter('excludeStatus', ShippingStatus::STATUS_CREATED)
            ;
            return $qb->getQuery()->getScalarResult();
        } catch (NoResultException $exc) {
            return null;
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
