<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Item;

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item as ItemEntity;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\MissingItemAttributeException;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Security\UserRepositoryInterface;
use DateTime;

class ItemManager
{
    protected static $productEntityName = 'SuluProductBundle:Product';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    public function __construct(
        ObjectManager $em,
        ItemRepository $itemRepository,
        UserRepositoryInterface $userRepository,
        RestHelperInterface $restHelper
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
        $this->restHelper = $restHelper;
    }

    /**
     * creates an item, but does not flush
     * @param array $data
     * @param $locale
     * @param $userId
     * @param \Sulu\Bundle\Sales\CoreBundle\Api\Item $item
     * @param null $itemStatusId
     * @internal param null $id
     * @return null|\Sulu\Bundle\Sales\CoreBundle\Api\Item
     */
    public function save(array $data, $locale, $userId = null, Item $item = null, $itemStatusId = null)
    {
        // check requiresd data
        $this->checkRequiredData($data, !!$item);

        // get item
        if (!$item) {
            $item = new Item(new ItemEntity(), $locale);
        }

        // get user
        $user = $userId ? $this->userRepository->findUserById($userId) : null;

        // get product and set Product's data to item
        $product = $this->setItemByProductData($data, $item, $locale);
        // if product is not set, set data manually
        if (!$product) {
            $item->setName($this->getProperty($data, 'name', $item->getName()));
            $item->setUseProductsPrice(false);
            $item->setQuantityUnit($this->getProperty($data, 'quantityUnit', null));
            $item->setTax($this->getProperty($data, 'tax', $item->getTax()));
        }
        $item->setPrice($this->getProperty($data, 'price', $item->getPrice()));

        // set item data
        $item->setQuantity($this->getProperty($data, 'quantity', null));
        $item->setDiscount($this->getProperty($data, 'discount', $item->getDiscount()));

        // create new item
        if ($item->getId() == null) {
            $item->setCreated(new DateTime());
            $item->setCreator($user);
            $this->em->persist($item->getEntity());

            if (!$itemStatusId = null) {
                $itemStatusId = ItemEntity::STATUS_CREATED;
            }
        }

        if ($itemStatusId) {
            $this->addStatus($item, $itemStatusId);
        }

        // handle attributes
        $this->processAttributes($data, $item, $locale);

        $item->setChanged(new DateTime());
        $item->setChanger($user);

        return $item;
    }

    /**
     * converts status of an item
     * @param Item $item
     * @param $status
     * @param bool $flush
     */
    public function addStatus(Item $item, $status, $flush = false)
    {
        // BITMASK
        $currentBitmaskStatus = $item->getBitmaskStatus();

        // if status is not already is in bitmask
        if (!($currentBitmaskStatus && $currentBitmaskStatus & $status)) {
            // add status
            $item->setBitmaskStatus($currentBitmaskStatus | $status);
        }

        if ($flush === true) {
            $this->em->flush();
        }
    }
    /**
     * converts status of an item
     * @param Item $item
     * @param $status
     * @param bool $flush
     */
    public function removeStatus(Item $item, $status, $flush = false)
    {
        // BITMASK
        $currentBitmaskStatus = $item->getBitmaskStatus();
        // if status is in bitmask, remove it
        if ($currentBitmaskStatus && $currentBitmaskStatus & $status) {
            $item->setBitmaskStatus($currentBitmaskStatus & ~$status);
        }

        if ($flush === true) {
            $this->em->flush();
        }
    }

    /**
     * deletes an item
     * @param $id
     * @throws Exception\ItemNotFoundException
     * @internal param $idπ
     *
     */
    public function delete($id)
    {
        $item = $this->itemRepository->findById($id);

        if (!$item) {
            throw new ItemNotFoundException($id);
        }

        $this->em->remove($item);
        $this->em->flush();
    }

    /**
     * Finds an item by id and locale
     * @param $id
     * @param $locale
     * @return null|Item
     */
    public function findByIdAndLocale($id, $locale)
    {
        $item = $this->itemRepository->findByIdAndLocale($id, $locale);

        if ($item) {
            return new Item($item, $locale);
        } else {
            return null;
        }
    }

    /**
     * Finds an item entity by id
     * @param $id
     * @return null|Item
     */
    public function findEntityById($id)
    {
        $item = $this->itemRepository->find($id);
        if (!$item) {
            return null;
        }

        return $item;
    }

    /**
     * @param $locale
     * @param array $filter
     * @return mixed
     */
    public function findAllByLocale($locale, $filter = array())
    {
        if (empty($filter)) {
            $items = $this->itemRepository->findAllByLocale($locale);
        } else {
            $items = $this->itemRepository->findByLocaleAndFilter($locale, $filter);
        }

        array_walk(
            $items,
            function (&$item) use ($locale){
                $item = new Item($item, $locale);
            }
        );

        return $items;
    }

    /**
     * check if necessary data is set
     * @param $data
     * @param $isNew
     */
    private function checkRequiredData($data, $isNew)
    {
        // either name or products must be set
        if (array_key_exists('product', $data)) {
            $this->checkDataSet($data['product'], 'id', $isNew);
        } else {
            $this->checkDataSet($data, 'name', $isNew);
        }
        $this->checkDataSet($data, 'quantity', $isNew);
        $this->checkDataSet($data, 'quantityUnit', $isNew);
    }

    /**
     * checks data for attributes
     * @param array $data
     * @param $key
     * @param $isNew
     * @throws Exception\MissingItemAttributeException
     * @return bool
     */
    private function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new MissingItemAttributeException($key);
        }

        return $keyExists;
    }

    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     * @param array $data
     * @param string $key
     * @param string $default
     * @return mixed
     */
    private function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Sets item based on given product data
     *
     * @param $data
     * @param Item $item
     * @param $locale
     * @throws Exception\MissingItemAttributeException
     * @throws \Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException
     * @return null|object
     */
    private function setItemByProductData($data, Item $item, $locale)
    {
        // terms of delivery
        $productData = $this->getProperty($data, 'product');
        if ($productData) {
            if (!array_key_exists('id', $productData)) {
                throw new MissingItemAttributeException('product.id');
            }
            /** @var Product $product */
            $product = $this->em->getRepository(self::$productEntityName)->find($productData['id']);
            if (!$product) {
                throw new ProductNotFoundException(self::$productEntityName, $productData['id']);
            }
            $item->setProduct($product);
            $translation = $product->getTranslation($locale);
            $item->setName($translation->getName());
            $item->setDescription($translation->getLongDescription());
            $item->setUseProductsPrice($this->getProperty($data, 'useProductsPrice', true));
            $item->setNumber($product->getNumber());

            // TODO: get products supplier and set it (when product has supplier relation)
//            $item->setSupplier($product->getSupplier);

            // TODO: get unit from product
            $item->setQuantityUnit('pc');
            // TODO: get tax from product
            $item->setTax(20);

            if ($item->getUseProductsPrice() === true) {
                $item->setPrice($product->getPrice());
            }

            return $product;
        } else {
            $item->setName(null);
            $item->setProduct(null);
        }
        return null;
    }

    /**
     * @param array $data
     * @param Item $item
     * @return bool
     * @throws ItemException
     */
    private function processAttributes($data, Item $item)
    {
        $result = true;
        try {
            if (isset($data['attributes']) && is_array($data['attributes'])) {
                /** @var ItemAttribute $itemAttribute */
                $get = function ($itemAttribute) {
                    return $itemAttribute->getId();
                };

                /** @var Item $item */
                $delete = function ($itemAttribute) use ($item) {
                    // delete item
                    $this->em->remove($itemAttribute);
                };

                /** @var ItemAttribute $itemAttribute */
                $update = function ($itemAttribute, $matchedEntry) use ($item) {
                    $itemAttribute->setAttribute($matchedEntry['attribute']);
                    $itemAttribute->setValue($matchedEntry['value']);
                    return $itemAttribute ? true : false;
                };

                $add = function ($itemData) use ($item) {
                    $itemAttribute = new ItemAttribute();
                    $itemAttribute->setAttribute($itemData['attribute']);
                    $itemAttribute->setValue($itemData['value']);
                    $itemAttribute->setItem($item->getEntity());
                    $this->em->persist($itemAttribute);
                    return $item->addAttribute($itemAttribute);
                };

                $result = $this->restHelper->processSubEntities(
                    $item->getAttributes(),
                    isset($data['attributes']) ? $data['attributes'] : array(),
                    $get,
                    $add,
                    $update,
                    $delete
                );
            }
        } catch (\Exception $e) {
            throw new ItemException('Error while creating attributes: ' . $e->getMessage());
        }
        return $result;
    }
}
