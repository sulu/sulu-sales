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
use Doctrine\ORM\EntityRepository;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductException;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException;
use Sulu\Bundle\ProductBundle\Product\ProductPriceManagerInterface;
use Sulu\Bundle\ProductBundle\Product\ProductRepositoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Api\ApiItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\MissingItemAttributeException;
use Sulu\Bundle\Sales\CoreBundle\Manager\OrderAddressManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\ItemPriceCalculator;
use Sulu\Component\Persistence\RelationTrait;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use DateTime;

class ItemManager
{
    use RelationTrait;

    protected static $productEntityName = 'SuluProductBundle:Product';

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
     * @var OrderAddressManager
     */
    protected $orderAddressManager;

    /**
     * @var string
     */
    protected $orderAddressEntity;

    /**
     * @var ItemFactoryInterface
     */
    protected $itemFactory;

    /**
     * @param ObjectManager $em
     * @param EntityRepository|ItemRepository $itemRepository
     * @param UserRepositoryInterface $userRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductPriceManagerInterface $productPriceManager
     * @param ItemPriceCalculator $itemPriceCalculator
     * @param OrderAddressManager $orderAddressManager
     * @param ItemFactoryInterface $itemFactory
     * @param string $orderAddressEntity
     */
    public function __construct(
        ObjectManager $em,
        EntityRepository $itemRepository,
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        ProductPriceManagerInterface $productPriceManager,
        ItemPriceCalculator $itemPriceCalculator,
        OrderAddressManager $orderAddressManager,
        ItemFactoryInterface $itemFactory,
        $orderAddressEntity
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->productPriceManager = $productPriceManager;
        $this->itemPriceCalculator = $itemPriceCalculator;
        $this->orderAddressManager = $orderAddressManager;
        $this->itemFactory = $itemFactory;
        $this->orderAddressEntity = $orderAddressEntity;
    }

    /**
     * Set correct product-entity to api-item
     *
     * @param $productEntity
     */
    public function setProductEntity($productEntity)
    {
        $entity = $this->itemApiEntity;
        $entity::$productEntity = $productEntity;
    }

    /**
     * Creates an item, but does not flush
     *
     * @param array $data
     * @param string $locale
     * @param int|null $userId
     * @param ApiItemInterface|null $item
     * @param int|null $itemStatusId
     *
     * @return ApiItemInterface|null
     */
    public function save(array $data, $locale, $userId = null, ApiItemInterface $item = null, $itemStatusId = null)
    {
        $itemEntity = $this->itemEntity;
        $isNewItem = !$item;

        // check required data
        if ($isNewItem) {
            $this->checkRequiredData($data, true);
            $item = $this->itemFactory->createApiEntity($this->itemFactory->createEntity(), $locale);
        }

        if ($item instanceof ItemInterface) {
            $item = $this->itemFactory->createApiEntity($item, $locale);
        }

        // get user
        $user = $userId ? $this->userRepository->findUserById($userId) : null;

        $contact = null;
        $account = null;
        if ($user) {
            $contact = $user->getContact();
            $account = $contact->getMainAccount();
        }

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
            if (is_array($data['deliveryAddress'])) {
                // if no delivery address is set, create new one
                if ($isNewItem || $item->getDeliveryAddress() === null) {
                    // create delivery address
                    $deliveryAddress = new $this->orderAddressEntity();
                    // persist entities
                    $this->em->persist($deliveryAddress);
                    // assign to order
                    $item->setDeliveryAddress($deliveryAddress);
                }

                $this->orderAddressManager->setOrderAddress(
                    $item->getDeliveryAddress(),
                    $data['deliveryAddress'],
                    $contact,
                    $account
                );
            } else {
                $deliveryAddress = $item->getEntity()->getDeliveryAddress();

                $orderAddress = $this->orderAddressManager->getOrderAddressByContactAddressId(
                    $data['deliveryAddress'],
                    $contact,
                    $account,
                    $deliveryAddress
                );

                // set delivery address
                $item->setDeliveryAddress($orderAddress);

                // if new delivery address persist
                if (!$deliveryAddress) {
                    $this->em->persist($orderAddress);
                }
            }
        }

        // create new item
        if ($item->getId() == null) {
            $item->setCreated(new DateTime());
            $item->setCreator($user);
            $this->em->persist($item->getEntity());

            if (!$itemStatusId = null) {
                $itemStatusId = $itemEntity::STATUS_CREATED;
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
     * Converts status of an item
     *
     * @param ApiItemInterface $item
     * @param int $status
     * @param bool $flush
     */
    public function addStatus(ApiItemInterface $item, $status, $flush = false)
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
     * Converts status of an item
     *
     * @param ApiItemInterface $item
     * @param int $status
     * @param bool $flush
     */
    public function removeStatus(ApiItemInterface $item, $status, $flush = false)
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
     * Deletes an item
     *
     * @param int $id
     *
     * @throws Exception\ItemNotFoundException
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
     *
     * @param int $id
     * @param string $locale
     *
     * @return null|ApiItemInterface
     */
    public function findByIdAndLocale($id, $locale)
    {
        $item = $this->itemRepository->findByIdAndLocale($id, $locale);

        if ($item) {
            return $this->itemFactory->createApiEntity($item, $locale);
        } else {
            return null;
        }
    }

    /**
     * Finds an item entity by id
     *
     * @param int $id
     *
     * @return null|ItemInterface
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
     * @param string $locale
     * @param array $filter
     *
     * @return array
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
                $item = $this->itemFactory->createApiEntity($item, $locale);
            }
        );

        return $items;
    }

    /**
     * Check if necessary data is set
     *
     * @param array $data
     * @param bool $isNew
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
     * Checks data for attributes
     *
     * @param array $data
     * @param string $key
     * @param bool $isNew
     *
     * @return bool
     * @throws Exception\MissingItemAttributeException
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
     *
     * @param array $data
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    private function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Returns productid - if not defined, throw an exception
     *
     * @param $data
     *
     * @return int
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
     * @param array $data
     * @param ApiItemInterface $item
     * @param string $locale
     *
     * @return null|ProductInterface
     * @throws MissingItemAttributeException
     * @throws ProductException
     * @throws ProductNotFoundException
     */
    private function setItemByProductData($data, ApiItemInterface $item, $locale)
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

            return $product;
        }

        return null;
    }

    /**
     * Function updates item prices its product data
     *
     * @param ApiItemInterface $item
     * @param array $data
     */
    private function updatePrices(ApiItemInterface $item, $data)
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
     * @param ApiItemInterface $item
     *
     * @return bool
     * @throws ItemException
     */
    private function processAttributes($data, ApiItemInterface $item)
    {
        $result = true;
        try {
            if (isset($data['attributes']) && is_array($data['attributes'])) {
                /** @var ItemAttribute $itemAttribute */
                $get = function ($itemAttribute) {
                    return $itemAttribute->getId();
                };

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
