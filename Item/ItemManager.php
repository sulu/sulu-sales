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
use Sulu\Bundle\ProductBundle\Product\Exception\ProductException;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException;
use Sulu\Bundle\ProductBundle\Product\ProductPriceManagerInterface;
use Sulu\Bundle\ProductBundle\Product\ProductRepositoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item as ItemEntity;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\MissingItemAttributeException;
use Sulu\Bundle\Sales\CoreBundle\Manager\BaseSalesManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\ItemPriceCalculator;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Component\Persistence\RelationTrait;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use DateTime;

class ItemManager extends BaseSalesManager
{
    use RelationTrait;

    protected static $productEntityName = 'SuluProductBundle:Product';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductPriceManagerInterface
     */
    protected $productPriceManager;

    /**
     * @var ItemPriceCalculator
     */
    protected $itemPriceCalculator;

    /**
     * constructor
     *
     * @param ObjectManager $em
     * @param ItemRepository $itemRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        ObjectManager $em,
        ItemRepository $itemRepository,
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        ProductPriceManagerInterface $productPriceManager,
        ItemPriceCalculator $itemPriceCalculator
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->productPriceManager = $productPriceManager;
        $this->itemPriceCalculator = $itemPriceCalculator;
    }

    /**
     * creates an item, but does not flush
     * @param array $data
     * @param $locale
     * @param $userId
     * @param \Sulu\Bundle\Sales\CoreBundle\Api\Item $item
     * @param null $itemStatusId
     * @return null|\Sulu\Bundle\Sales\CoreBundle\Api\Item
     */
    public function save(array $data, $locale, $userId = null, $item = null, $itemStatusId = null)
    {
        $isNewItem = !$item; 
        
        // check required data
        if ($isNewItem) {
            $this->checkRequiredData($data, true);
            $item = new Item(new ItemEntity(), $locale);
        }

        if ($item instanceof ItemEntity) {
            $item = new Item($item, $locale);
        }

        // get user
        $user = $userId ? $this->userRepository->findUserById($userId) : null;

        // set item data
        $item->setQuantity($this->getProperty($data, 'quantity', null));

        $product = null;
        if ($isNewItem) {
            // get product and set Product's data to item
            $product = $this->setItemByProductData($data, $item, $locale);
        }

        // if product is not set, set data manually
        if (!$product) {
            if ($isNewItem) {
                $item->setUseProductsPrice(false);
            }
            $item->setName($this->getProperty($data, 'name', $item->getName()));
            // TODO: set supplier based on if its a string  or object (fetch account and set it to setSupplier)
            $item->setSupplierName($this->getProperty($data, 'supplierName', $item->getSupplierName()));
            $item->setTax($this->getProperty($data, 'tax', $item->getTax()));
            $item->setNumber($this->getProperty($data, 'number', $item->getNumber()));
            $item->setDescription($this->getProperty($data, 'description', $item->getDescription()));
            $item->setQuantityUnit($this->getProperty($data, 'quantityUnit', $item->getQuantityUnit()));
        }

        // update prices
        $this->updatePrices($item, $data);

        $item->setDiscount($this->getProperty($data, 'discount', $item->getDiscount()));

        // set delivery-address
        if (isset($data['deliveryAddress'])) {
            if ($isNewItem || $item->getDeliveryAddress() === null) {
                // create delivery address
                $deliveryAddress = new OrderAddress();
                // persist entities
                $this->em->persist($deliveryAddress);
                // assign to order
                $item->setDeliveryAddress($deliveryAddress);
            }

            $this->setOrderAddress(
                $item->getDeliveryAddress(),
                $data['deliveryAddress'],
                $customerContact,
                $customerAccount
            );
        }

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
            function (&$item) use ($locale) {
                $item = new Item($item, $locale);
            }
        );

        return $items;
    }

    /**
     * check if necessary data is set
     *
     * @param $data
     * @param $isNew
     *
     * @throws MissingItemAttributeException
     */
    private function checkRequiredData($data, $isNew)
    {
        // either name or products must be set
        if (array_key_exists('product', $data)) {
            // product-id must be defined
            $this->getProductId($data['product']);
        } else {
            $this->checkDataSet($data, 'name', $isNew);
            $this->checkDataSet($data, 'quantityUnit', $isNew);
        }
        $this->checkDataSet($data, 'quantity', $isNew);
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
     * returns productid - if not defined, throw an exception
     *
     * @param $data
     *
     * @return mixed
     * @throws MissingItemAttributeException
     */
    private function getProductId($data)
    {
        // if data is array, id must be a key
        if (is_array($data)) {
            if (!isset($data['id'])) {
                throw new MissingItemAttributeException('product.id');
            }

            return $data['id'];
            // data must be an int
        } elseif (!is_int($data)) {
            throw new MissingItemAttributeException('product.id');
        }

        return $data;
    }

    /**
     * Sets item based on given product data
     *
     * @param $data
     * @param Item $item
     * @param $locale
     * @return null|object
     * @throws MissingItemAttributeException
     * @throws ProductException
     * @throws ProductNotFoundException
     */
    private function setItemByProductData($data, Item $item, $locale)
    {
        // terms of delivery
        $productData = $this->getProperty($data, 'product');
        if ($productData) {
            $productId = $this->getProductId($productData);
            
            /** @var Product $product */
            $product = $this->productRepository->find($productId);
            if (!$product) {
                throw new ProductNotFoundException(self::$productEntityName, $productId);
            }
            $item->setProduct($product);
            $translation = $product->getTranslation($locale);

            // when the product is not available in the current language choose the first translation you find
            // FIXME: should be changed when products have a language fallback
            // https://github.com/massiveart/POOL-ALPIN/issues/611
            if (is_null($translation)) {
                if (count($product->getTranslations()) > 0) {
                    $translation = $product->getTranslations()[0];
                } else {
                    throw new ProductException('Product ' . $product->getId() . ' has no translations!');
                }
            }

            $item->setName($translation->getName());
            $item->setDescription($translation->getLongDescription());
            $item->setUseProductsPrice($this->getProperty($data, 'useProductsPrice', true));
            $item->setNumber($product->getNumber());

            // get products supplier
            if ($product->getSupplier()) {
                $item->setSupplier($product->getSupplier());
                $item->setSupplierName($product->getSupplier()->getName());
            } else {
                $item->setSupplier(null);
                $item->setSupplierName('');
            }

            // set order unit
            if ($product->getOrderUnit()) {
                $item->setQuantityUnit($product->getOrderUnit()->getTranslation($locale)->getName());
            }

            // TODO: get tax from product
            $item->setTax(0);
//            $item->setTax($product->getTaxClass()->getTax($locale));

            return $product;
        }
        return null;
    }

    /**
     * function updates item by its product data
     *
     * @param $item
     */
    private function updatePrices($item, $data)
    {
        //TODO: currency
        $currency = null;

        // set products price by data
        if ($item->getUseProductsPrice() === false) {
            $item->setPrice($this->getProperty($data, 'price', $item->getPrice()));
        }
        
        // set items total net price
        $price = $this->itemPriceCalculator->calculate($item, $currency, $item->getUseProductsPrice());
        $item->setTotalNetPrice($price);

        // set items price
        $itemPrice = $this->itemPriceCalculator->getItemPrice($item, $currency, $item->getUseProductsPrice());
        $item->setPrice($itemPrice);
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

                $result = $this->processSubEntities(
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
