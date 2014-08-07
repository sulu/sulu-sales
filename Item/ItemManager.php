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
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\MissingItemAttributeException;
use Sulu\Component\Security\UserRepositoryInterface;
use DateTime;

class ItemManager
{
    protected static $productEntityName = 'SuluProductBundle:Product';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $itemStatusEntityName = 'SuluSalesCoreBundle:ItemStatus';
    protected static $itemStatusTranslationEntityName = 'SuluSalesCoreBundle:ItemStatusTranslation';

    private $currentLocale;

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
     * Describes the fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    public function __construct(
        ObjectManager $em,
        ItemRepository $itemRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param array $data
     * @param $locale
     * @param $userId
     * @param null $id
     * @throws Exception\ItemNotFoundException
     * @throws Exception\MissingItemAttributeException
     * @return null|\Sulu\Bundle\Sales\CoreBundle\Api\Item
     */
    public function save(array $data, $locale, $userId, $id = null)
    {

        // check requiresd data
        $this->checkRequiredData($data, $id === null);

        // get item
        if ($id) {
            $item = $this->findByIdAndLocale($id, $locale);
            if (!$item) {
                throw new ItemNotFoundException($id);
            }
        } else {
            $item = new Item(new ItemEntity(), $locale);
        }

        // get user
        $user = $this->userRepository->findUserById($userId);

        // get product and set Product's data to item
        $product = $this->setProduct($data, $item, $locale);
        // if product is not set, set data manually
        if (!$product) {
            $item->setName($this->getProperty($data, 'name', $item->getName()));
            $item->setUseProductsPrice(false);
            $item->setQuantityUnit($this->getProperty($data, 'pc', null));
            $item->setTax($this->getProperty($data, 'tax', $item->getTax()));
            $item->setPrice($this->getProperty($data, 'price', $item->getPrice()));
        }

        // set item data
        $item->setQuantity($this->getProperty($data, 'quantity', null));
        $item->setDiscount($this->getProperty($data, 'discount', $item->getDiscount()));

        // create new item
        if ($item->getId() == null) {
            $item->setCreated(new DateTime());
            $item->setCreator($user);
            $this->em->persist($item->getEntity());

            // TODO: determine item status
            // FIXME: currently the status with id=1 is taken
            $status = $this->em->getRepository(self::$itemStatusEntityName)->find(1);
            $item->setStatus($status);
        }

        $item->setChanged(new DateTime());
        $item->setChanger($user);

        $this->em->flush();

        return $item;
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
     * @param $locale
     * @param array $filter
     * @return mixed
     */
    public function findAllByLocale($locale, $filter = array())
    {
        if (empty($filter)) {
            $item = $this->itemRepository->findAllByLocale($locale);
        } else {
            $item = $this->itemRepository->findByLocaleAndFilter($locale, $filter);
        }

        array_walk(
            $item,
            function (&$item) use ($locale){
                $item = new Item($item, $locale);
            }
        );

        return $item;
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
        $this->checkDataSet($data, 'name', $isNew);
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
     * @param $data
     * @param Item $item
     * @param $locale
     * @throws Exception\MissingItemAttributeException
     * @throws \Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException
     * @return null|object
     */
    private function setProduct($data, Item $item, $locale)
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
            $item->setName($product);
            $item->setUseProductsPrice($data['useProductsPrice']);
            if ($item->getUseProductsPrice() === true) {
                $item->setPrice($product->getPrice());
                $item->setNumber($product->getNumber());
                $item->setDescription($product->getTranslation($locale)->getLongDescription());
            }
//            $item->setTax($product->getTax())

            return $product;
        } else {
            $item->setName(null);
            $item->setProduct(null);
        }
        return null;

    }
}
