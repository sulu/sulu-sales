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

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ProductBundle\Entity\Addon;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Entity\ProductAddonRepository;
use Sulu\Bundle\ProductBundle\Entity\ProductInterface;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductException;
use Sulu\Bundle\ProductBundle\Product\Exception\ProductNotFoundException;
use Sulu\Bundle\ProductBundle\Product\ProductPriceManagerInterface;
use Sulu\Bundle\ProductBundle\Product\ProductRepositoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Api\ApiItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\BaseItem;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemDependencyNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\MissingItemAttributeException;
use Sulu\Bundle\Sales\CoreBundle\Manager\OrderAddressManager;
use Sulu\Bundle\PricingBundle\Pricing\ItemPriceCalculator;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Persistence\RelationTrait;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Bundle\ProductBundle\Entity\TaxClass;
use Sulu\Bundle\ProductBundle\Entity\CountryTaxRepository;

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
     * @var CountryTaxRepository
     */
    protected $countryTaxRepository;

    /**
     * @var string
     */
    protected $orderAddressEntity;

    /**
     * @var ItemFactoryInterface
     */
    protected $itemFactory;

    /**
     * @var string
     */
    protected $shopLocation;

    /**
     * @var ProductAddonRepository
     */
    protected $addonRepository;

    /**
     * @param ObjectManager $em
     * @param EntityRepository|ItemRepository $itemRepository
     * @param UserRepositoryInterface $userRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductPriceManagerInterface $productPriceManager
     * @param ItemPriceCalculator $itemPriceCalculator
     * @param OrderAddressManager $orderAddressManager
     * @param CountryTaxRepository $countryTaxRepository
     * @param ItemFactoryInterface $itemFactory
     * @param ProductAddonRepository $addonRepository
     * @param string $orderAddressEntity
     * @param string $shopLocation
     */
    public function __construct(
        ObjectManager $em,
        EntityRepository $itemRepository,
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        ProductPriceManagerInterface $productPriceManager,
        ItemPriceCalculator $itemPriceCalculator,
        OrderAddressManager $orderAddressManager,
        CountryTaxRepository $countryTaxRepository,
        ItemFactoryInterface $itemFactory,
        ProductAddonRepository $addonRepository,
        $orderAddressEntity,
        $shopLocation
    ) {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->productPriceManager = $productPriceManager;
        $this->itemPriceCalculator = $itemPriceCalculator;
        $this->orderAddressManager = $orderAddressManager;
        $this->countryTaxRepository = $countryTaxRepository;
        $this->itemFactory = $itemFactory;
        $this->addonRepository = $addonRepository;
        $this->orderAddressEntity = $orderAddressEntity;
        $this->shopLocation = $shopLocation;
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
     * @param ApiItemInterface|ItemInterface|null $item
     * @param int|null $itemStatusId
     * @param ContactInterface|null $contact The contact that should be used for order-address
     *
     * @return ApiItemInterface
     *
     * @throws ItemException
     * @throws ProductException
     * @throws ProductNotFoundException
     * @throws \Exception
     */
    public function save(
        array $data,
        $locale,
        $userId = null,
        $item = null,
        $itemStatusId = null,
        ContactInterface $contact = null
    ) {
        $itemEntity = $this->itemFactory->createEntity();
        $isNewItem = !$item;

        // Check if required data for creating an item is set.
        if ($isNewItem) {
            $this->checkRequiredData($data, true);
            $item = $this->itemFactory->createApiEntity($this->itemFactory->createEntity(), $locale);
        }

        if ($item instanceof ItemInterface) {
            $item = $this->itemFactory->createApiEntity($item, $locale);
        }

        $user = $userId ? $this->userRepository->findUserById($userId) : null;

        $account = null;
        // If no contact is given take user as fallback.
        if (!$contact && !!$user) {
            $contact = $user->getContact();
        }
        if ($contact) {
            $account = $contact->getMainAccount();
        }

        $item->setQuantity($this->getProperty($data, 'quantity'));
        $item->setUseProductsPrice($this->getProperty($data, 'useProductsPrice', true));
        $this->setDate(
            $data,
            'deliveryDate',
            null,
            [$item, 'setDeliveryDate']
        );

        $item->setType($this->getProperty($data, 'type', BaseItem::TYPE_PRODUCT));

        // Check if the product or addon relation on a saved item still exists, else set type custom.
        if (!$isNewItem) {
            if ($item->getType() === BaseItem::TYPE_PRODUCT && $item->getProduct() === null) {
                $item->setType(BaseItem::TYPE_CUSTOM);
            } elseif ($item->getType() === BaseItem::TYPE_ADDON && $item->getAddon() === null) {
                $item->setType(BaseItem::TYPE_CUSTOM);
            }
        }

        switch ($item->getType()) {
            case BaseItem::TYPE_PRODUCT:
                if ($isNewItem) {
                    $productData = $this->getProperty($data, 'product');
                    if ($productData) {
                        // Set Product's data to item
                        $this->setItemByProductData($productData, $item, $locale);
                    }
                }
                break;
            case BaseItem::TYPE_CUSTOM:
                if ($isNewItem) {
                    $item->setUseProductsPrice(false);
                }
                $item->setName($this->getProperty($data, 'name', $item->getName()));
                $item->setTax($this->getProperty($data, 'tax', $item->getTax()));
                $item->setNumber($this->getProperty($data, 'number', $item->getNumber()));
                $item->setDescription($this->getProperty($data, 'description', $item->getDescription()));
                $item->setQuantityUnit($this->getProperty($data, 'quantityUnit', $item->getQuantityUnit()));

                break;
            case BaseItem::TYPE_ADDON:
                if ($isNewItem) {
                    $addonData = $this->getProperty($data, 'addon');
                    if ($addonData) {
                        $this->setItemByAddonData($addonData, $item, $locale);
                    }
                }
                break;
            default:
                throw new ItemException('Unhandled item type found');
                break;
        }

        if (method_exists($item, 'getSupplierName')) {
            $item->setSupplierName($this->getProperty($data, 'supplierName', $item->getSupplierName()));
        }

        // Update prices.
        $this->updatePrices($item, $data);

        $item->setDiscount($this->getProperty($data, 'discount', $item->getDiscount()));
        $item->setIsRecurringPrice($this->getProperty($data, 'isRecurringPrice', $item->isRecurringPrice()));

        $item->setCostCentre($this->getProperty($data, 'costCentre'));

        // Set delivery-address for item.
        if (isset($data['deliveryAddress'])) {
            $this->setItemDeliveryAddress($data['deliveryAddress'], $item, $contact, $account);
        }

        // Create new item.
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

        // Handle attributes.
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
        }

        return null;
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
     * Retrieves the tax depending on the current class and configured
     * shop location.
     *
     * @param TaxClass $taxClass
     *
     * @return float
     */
    public function retrieveTaxForClass(TaxClass $taxClass)
    {
        $locale = $this->shopLocation;
        $countryTax = $this->countryTaxRepository->findByLocaleAndTaxClassId($locale, $taxClass->getId());
        if (!$countryTax) {
            $countryTax = $this->countryTaxRepository->findByLocaleAndTaxClassId($locale, TaxClass::STANDARD_TAX_RATE);
            if (!$countryTax) {
                return 0;
            }
        }

        return $countryTax->getTax();
    }

    /**
     * Sets delivery address for an item.
     *
     * @param array|int $addressData
     * @param ApiItemInterface $item
     * @param ContactInterface $contact
     * @param AccountInterface $account
     *
     * @throws ItemDependencyNotFoundException
     * @throws ItemException
     */
    protected function setItemDeliveryAddress(
        $addressData,
        ApiItemInterface $item,
        ContactInterface $contact = null,
        AccountInterface $account = null
    ) {
        if ($item->getDeliveryAddress() === null) {
            // Create new delivery address.
            $deliveryAddress = new $this->orderAddressEntity();
            // Persist entities.
            $this->em->persist($deliveryAddress);
            // Assign to order.
            $item->setDeliveryAddress($deliveryAddress);
        }

        if (is_array($addressData)) {
                // Set order-address.
                $this->orderAddressManager->setOrderAddress(
                    $item->getDeliveryAddress(),
                    $addressData,
                    $contact,
                    $account
                );
        } elseif (is_int($addressData)) {
            $contactAddressId = $addressData;
            // Create order-address and assign contact-address data.
            $deliveryAddress = $item->getEntity()->getDeliveryAddress();

            $orderAddress = $this->orderAddressManager->getAndSetOrderAddressByContactAddressId(
                $contactAddressId,
                $contact,
                $account,
                $deliveryAddress
            );

            // Set delivery address.
            $item->setDeliveryAddress($orderAddress);

            // If new delivery address persist.
            if (!$deliveryAddress) {
                $this->em->persist($orderAddress);
            }
        }
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
            $this->getProductId($data['product'], 'product.id');
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
    protected function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * @param array $data
     * @param string $exceptionKey
     *
     * @return int
     *
     * @throws MissingItemAttributeException
     */
    private function getProductId($data, $exceptionKey)
    {
        // if data is array, id must be a key
        if (is_array($data)) {
            if (!isset($data['id'])) {
                throw new MissingItemAttributeException($exceptionKey);
            }

            return $data['id'];
            // data must be an int
        } elseif (!is_int($data)) {
            throw new MissingItemAttributeException($exceptionKey);
        }

        return $data;
    }

    /**
     * Sets item based on given product data
     *
     * @param array $productData
     * @param ApiItemInterface $item
     * @param string $locale
     *
     * @throws MissingItemAttributeException
     * @throws ProductException
     * @throws ProductNotFoundException
     *
     * @return ProductInterface
     */
    protected function setItemByProductData($productData, ApiItemInterface $item, $locale)
    {
        $productId = $this->getProductId($productData, 'product.id');

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

        $this->setItemSupplier($item, $product);

        $item->setName($translation->getName());
        $item->setDescription($translation->getLongDescription());
        $item->setNumber($product->getNumber());

        // set order unit
        if ($product->getOrderUnit()) {
            $item->setQuantityUnit($product->getOrderUnit()->getTranslation($locale)->getName());
        }

        $taxClass = $product->getTaxClass();
        if ($taxClass) {
            $tax = $this->retrieveTaxForClass($taxClass);
            $item->setTax($tax);
        } else {
            $item->setTax(0);
        }

        return $product;
    }

    /**
     * @param array $addonData
     * @param ApiItemInterface $item
     * @param string $locale
     *
     * @return Addon
     *
     * @throws MissingItemAttributeException
     * @throws ProductException
     * @throws \Exception
     */
    protected function setItemByAddonData($addonData, ApiItemInterface $item, $locale)
    {
        $addonId = $this->getProductId($addonData, 'addon.id');

        /** @var Addon $addon */
        $addon = $this->addonRepository->find($addonId);
        if (!$addon) {
            throw new \Exception('Addon with id ' . $addonId . ' not found.');
        }

        $item->setAddon($addon);
        $addonProduct = $addon->getAddon();

        $translation = $addonProduct->getTranslation($locale);
        if (is_null($translation) && count($addonProduct->getTranslations()) === 0) {
            throw new ProductException('Product ' . $addonProduct->getId() . ' has no translations!');
        }
        $translation = $addonProduct->getTranslations()[0];

        $item->setName($translation->getName());
        $item->setDescription($translation->getLongDescription());
        $item->setNumber($addonProduct->getNumber());

        // set order unit
        if ($addonProduct->getOrderUnit()) {
            $item->setQuantityUnit($addonProduct->getOrderUnit()->getTranslation($locale)->getName());
        }

        $tax = 0;
        $taxClass = $addonProduct->getTaxClass();
        if ($taxClass) {
            $tax = $this->retrieveTaxForClass($taxClass);
        }
        $item->setTax($tax);

        return $addon;
    }

    /**
     * Set supplier of an item
     *
     * @param ItemInterface $item
     * @param ProductInterface $product
     */
    protected function setItemSupplier($item, $product)
    {
        $supplier = null;
        $supplierName = '';
        // get products supplier
        if ($product->getSupplier()) {
            $supplier = $product->getSupplier();
            $supplierName = $product->getSupplier()->getName();
        }
        $item->setSupplier($supplier);
        $item->setSupplierName($supplierName);
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

    /**
     * Sets a date if it's set in data
     *
     * @param array $data
     * @param string $key
     * @param DateTime $currentDate
     * @param callable $setCallback
     */
    protected function setDate($data, $key, $currentDate, callable $setCallback)
    {
        $date = $this->getProperty($data, $key, $currentDate);
        if ($date !== null) {
            if (is_string($date)) {
                $date = new DateTime($data[$key]);
            }
        }

        call_user_func($setCallback, $date);
    }
}
