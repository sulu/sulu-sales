<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Tests;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sulu\Bundle\ContactExtensionBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\AccountContact;
use Sulu\Bundle\ContactBundle\Entity\AccountAddress;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactTitle;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\ContactBundle\Entity\Phone;
use Sulu\Bundle\ContactBundle\Entity\PhoneType;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;
use Sulu\Bundle\ProductBundle\Entity\Addon;
use Sulu\Bundle\ProductBundle\Entity\AddonPrice;
use Sulu\Bundle\ProductBundle\Entity\Currency;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Entity\ProductPrice;
use Sulu\Bundle\ProductBundle\Entity\ProductTranslation;
use Sulu\Bundle\ProductBundle\Entity\Status;
use Sulu\Bundle\ProductBundle\Entity\StatusTranslation;
use Sulu\Bundle\ProductBundle\Entity\Type;
use Sulu\Bundle\ProductBundle\Entity\TypeTranslation;
use Sulu\Bundle\ProductBundle\Entity\Unit;
use Sulu\Bundle\ProductBundle\Entity\UnitTranslation;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\Sales\OrderBundle\DataFixtures\ORM\LoadOrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderType;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Contact\Model\ContactRepositoryInterface;

class OrderDataSetup
{
    public $locale = 'en';

    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';

    /**
     * @var OrderType
     */
    public $orderTypeManual;
    public $orderTypeShop;
    public $orderTypeAnon;

    /**
     * @var Account
     */
    public $account;

    /**
     * @var Account
     */
    public $account2;

    /**
     * @var AccountContact
     */
    public $accountContact;

    /**
     * @var AccountContact
     */
    public $accountContact2;

    /**
     * @var Address
     */
    public $address;

    /**
     * @var Address
     */
    public $address2;

    /**
     * @var ContactInterface
     */
    public $contact;

    /**
     * @var ContactInterface
     */
    public $contact2;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var OrderAddress
     */
    public $orderAddressDelivery;

    /**
     * @var OrderAddress
     */
    public $orderAddressInvoice;

    /**
     * @var OrderStatus
     */
    public $orderStatus;

    /**
     * @var TermsOfDelivery
     */
    public $termsOfDelivery;

    /**
     * @var TermsOfPayment
     */
    public $termsOfPayment;

    /**
     * @var Item
     */
    public $item;

    /**
     * @var Item
     */
    public $item2;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var Product
     */
    public $product2;

    /**
     * @var ProductTranslation
     */
    public $productTranslation;

    /**
     * @var Phone
     */
    public $phone;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var ProductPrice
     */
    public $productPrice;

    /**
     * @var string
     */
    public $defaultCurrencyCode;

    /**
     * @var Addon
     */
    public $addon;

    /**
     * @var AddonPrice
     */
    public $addonPrice;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ItemFactoryInterface
     */
    protected $itemFactory;

    /**
     * @var string
     */
    protected $productEntity;

    /**
     * @var ContactRepositoryInterface
     */
    protected $contactRepository;

    /**
     * OrderDataSetup constructor.
     *
     * @param EntityManager $entityManager
     * @param ItemFactoryInterface $itemFactory
     * @param ContactRepositoryInterface $contactRepository
     * @param string $productEntity
     * @param string $defaultCurrencyCode
     */
    public function __construct(
        EntityManager $entityManager,
        ItemFactoryInterface $itemFactory,
        ContactRepositoryInterface $contactRepository,
        $productEntity = 'Sulu\Bundle\ProductBundle\Entity\Product',
        $defaultCurrencyCode = 'EUR'
    ) {
        $this->productEntity = $productEntity;
        $this->itemFactory = $itemFactory;
        $this->defaultCurrencyCode = $defaultCurrencyCode;
        $this->contactRepository = $contactRepository;

        $this->em = $entityManager;

        $this->loadFixtures();
        $this->setUpTestData();
    }

    /**
     * Setup tests for cart testing.
     */
    public function setupCartTests()
    {
        // Set order to cart order.
        $this->orderStatus = $this->em
            ->getRepository(static::$orderStatusEntityName)
            ->find(OrderStatus::STATUS_IN_CART);

        $this->order->setStatus($this->orderStatus);
        $this->order->setSessionId('IamASessionKey');

        $this->em->flush();
    }

    /**
     * Load all fixtures necessary for testing.
     */
    protected function loadFixtures()
    {
        // Load order-status
        $statusFixtures = new LoadOrderStatus();
        $statusFixtures->load($this->em);
    }

    /**
     * Setup test data.
     */
    protected function setUpTestData()
    {
        // Account
        $this->account = new Account();
        $this->account->setName('Company');
        $this->account->setType(Account::TYPE_BASIC);
        $this->account->setUid('uid-123');
        $this->account->setMainEmail('test@test.com');

        $this->account2 = clone($this->account);

        // Country
        $country = new Country();
        $country->setName('Country');
        $country->setCode('co');
        // Address type
        $addressType = new AddressType();
        $addressType->setName('Business');
        // Address
        $this->address = new Address();
        $this->address->setStreet('Sample-Street');
        $this->address->setNumber('12');
        $this->address->setAddition('Entrance 2');
        $this->address->setCity('Sample-City');
        $this->address->setState('State');
        $this->address->setZip('12345');
        $this->address->setCountry($country);
        $this->address->setPostboxNumber('postboxNumber');
        $this->address->setPostboxPostcode('postboxPostcode');
        $this->address->setPostboxCity('postboxCity');
        $this->address->setAddressType($addressType);
        // Address
        $this->address2 = new Address();
        $this->address2->setStreet('Street');
        $this->address2->setNumber('2');
        $this->address2->setCity('Utopia');
        $this->address2->setZip('1');
        $this->address2->setCountry($country);
        $this->address2->setAddressType($addressType);

        // Add address to entities.
        $accountAddress = new AccountAddress();
        $accountAddress->setAccount($this->account);
        $accountAddress->setAddress($this->address);
        $accountAddress->setMain(true);
        $this->account->addAccountAddress($accountAddress);

        // Phone
        $phoneType = new PhoneType();
        $phoneType->setName('Business');
        $this->phone = new Phone();
        $this->phone->setPhone('+43 123 / 456 789');
        $this->phone->setPhoneType($phoneType);

        // Contact Title
        $title = new ContactTitle();
        $title->setTitle('Dr');

        // Contact
        $this->contact = $this->contactRepository->createNew();
        $this->contact->setFirstName('John');
        $this->contact->setLastName('Doe');
        $this->contact->setTitle($title);
        $this->contact->setMainEmail('test@test.com');
        // Second Contact
        $this->contact2 = $this->contactRepository->createNew();
        $this->contact2->setFirstName('Johanna');
        $this->contact2->setLastName('Dole');
        $this->contact2->setMainEmail('test@test.com');

        $contact = $this->contactRepository->createNew();
        $contact->setFirstName('Max');
        $contact->setLastName('Mustermann');
        $this->em->persist($contact);

        $this->accountContact = $this->createAccountContact($this->account, $this->contact, true);
        $this->accountContact2 = $this->createAccountContact($this->account, $this->contact2, true);

        $user = new User();
        $user->setUsername('test');
        $user->setPassword('test');
        $user->setSalt('');
        $user->setLocale('en');
        $user->setContact($this->contact);
        $this->user = $user;

        $this->orderStatus = $this->em->getRepository(self::$orderStatusEntityName)->find(OrderStatus::STATUS_CREATED);

        // Order address
        $this->orderAddressDelivery = new OrderAddress();
        $this->orderAddressDelivery->setFirstName($this->contact->getFirstName());
        $this->orderAddressDelivery->setLastName($this->contact->getLastName());
        $this->orderAddressDelivery->setTitle($title->getTitle());
        $this->orderAddressDelivery->setStreet($this->address->getStreet());
        $this->orderAddressDelivery->setNumber($this->address->getNumber());
        $this->orderAddressDelivery->setAddition($this->address->getAddition());
        $this->orderAddressDelivery->setCity($this->address->getCity());
        $this->orderAddressDelivery->setZip($this->address->getZip());
        $this->orderAddressDelivery->setState($this->address->getState());
        $this->orderAddressDelivery->setCountry($this->address->getCountry()->getName());
        $this->orderAddressDelivery->setPostboxNumber($this->address->getPostboxNumber());
        $this->orderAddressDelivery->setPostboxPostcode($this->address->getPostboxPostcode());
        $this->orderAddressDelivery->setPostboxCity($this->address->getPostboxCity());
        $this->orderAddressDelivery->setAccountName($this->account->getName());
        $this->orderAddressDelivery->setUid($this->account->getUid());
        $this->orderAddressDelivery->setPhone($this->phone->getPhone());
        $this->orderAddressDelivery->setPhoneMobile('+43 123 / 456');
        $this->orderAddressDelivery->setContactAddress($this->address);

        // Clone address for invoice.
        $this->orderAddressInvoice = clone $this->orderAddressDelivery;

        $this->termsOfDelivery = new TermsOfDelivery();
        $this->termsOfDelivery->setTerms('10kg minimum');
        $this->termsOfPayment = new TermsOfPayment();
        $this->termsOfPayment->setTerms('10% off');

        // Order
        $this->order = $this->createNewTestOrder();

        $order2 = $this->createNewTestOrder();
        $order2->setNumber('12345');
        $order2->setDeliveryAddress(null);
        $order2->setInvoiceAddress(null);

        // Product order unit
        $orderUnit = new Unit();
        $orderUnit->setId(1);
        $orderUnitTranslation = new UnitTranslation();
        $orderUnitTranslation->setUnit($orderUnit);
        $orderUnitTranslation->setName('pc');
        $orderUnitTranslation->setLocale('en');
        $orderUnit->addTranslation($orderUnitTranslation);
        $this->em->persist($orderUnit);
        $this->em->persist($orderUnitTranslation);
        // Product type
        $productType = new Type();
        $productTypeTranslation = new TypeTranslation();
        $productTypeTranslation->setLocale($this->locale);
        $productTypeTranslation->setName('EnglishProductType-1');
        $productTypeTranslation->setType($productType);
        // Product status
        $productStatus = new Status();
        $productStatus->setId(Status::ACTIVE);
        $metadata = $this->em->getClassMetadata(Status::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $productStatusTranslation = new StatusTranslation();
        $productStatusTranslation->setLocale($this->locale);
        $productStatusTranslation->setName('EnglishProductStatus-1');
        $productStatusTranslation->setStatus($productStatus);
        // Product
        $this->product = new $this->productEntity;
        $this->product->setNumber('ProductNumber-1');
        $this->product->setManufacturer('EnglishManufacturer-1');
        $this->product->setType($productType);
        $this->product->setStatus($productStatus);
        $this->product->setCreated(new DateTime());
        $this->product->setChanged(new DateTime());
        $this->product->setSupplier($this->account);
        $this->product->setOrderUnit($orderUnit);

        // Product translation
        $this->productTranslation = new ProductTranslation();
        $this->productTranslation->setProduct($this->product);
        $this->productTranslation->setLocale($this->locale);
        $this->productTranslation->setName('EnglishProductTranslationName-1');
        $this->productTranslation->setShortDescription('EnglishProductShortDescription-1');
        $this->productTranslation->setLongDescription('EnglishProductLongDescription-1');
        $this->product->addTranslation($this->productTranslation);

        // Product
        $this->product2 = clone($this->product);
        $this->product2->setSupplier($this->account);
        $translation2 = clone($this->productTranslation);
        $translation2->setProduct($this->product2);
        $this->product2->addTranslation($translation2);
        $this->em->persist($translation2);

        $this->currency = new Currency();
        $this->currency->setCode($this->defaultCurrencyCode);
        $this->currency->setNumber('1');
        $this->currency->setId('1');
        $this->currency->setName('Euro');

        $this->productPrice = new ProductPrice();
        $this->productPrice->setCurrency($this->currency);
        $this->productPrice->setMinimumQuantity(0);
        $this->productPrice->setPrice(14.5);
        $this->productPrice->setProduct($this->product);
        $this->product->addPrice($this->productPrice);

        $price2 = clone($this->productPrice);
        $price2->setProduct($this->product2);
        $price2->setPrice(15.5);

        $this->em->persist($price2);
        $this->product2->addPrice($price2);

        // Item
        $this->item = $this->createNewTestItem();

        $this->item2 = $this->createNewTestItem();
        $this->item2->setSupplier($this->account2);

        $orderTypeTranslationManual = new OrderTypeTranslation();
        $orderTypeTranslationManual->setLocale('en');
        $orderTypeTranslationManual->setName('order type translation manual');

        $orderTypeTranslationShop = new OrderTypeTranslation();
        $orderTypeTranslationShop->setLocale('en');
        $orderTypeTranslationShop->setName('order type translation shop');

        $orderTypeTranslationAnon = new OrderTypeTranslation();
        $orderTypeTranslationAnon->setLocale('en');
        $orderTypeTranslationAnon->setName('order type translation anon');

        $this->orderTypeManual = new OrderType();
        $metadata = $this->em->getClassMetadata(get_class($this->orderTypeManual));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->orderTypeManual->setId(OrderType::MANUAL);
        $this->orderTypeManual->addTranslation($orderTypeTranslationManual);
        $orderTypeTranslationManual->setType($this->orderTypeManual);

        $this->orderTypeShop = new OrderType();
        $metadata = $this->em->getClassMetadata(get_class($this->orderTypeShop));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->orderTypeShop->setId(OrderType::SHOP);
        $this->orderTypeShop->addTranslation($orderTypeTranslationShop);
        $orderTypeTranslationShop->setType($this->orderTypeShop);

        $this->orderTypeAnon = new OrderType();
        $metadata = $this->em->getClassMetadata(get_class($this->orderTypeAnon));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->orderTypeAnon->setId(OrderType::ANONYMOUS);
        $this->orderTypeAnon->addTranslation($orderTypeTranslationAnon);
        $orderTypeTranslationAnon->setType($this->orderTypeAnon);

        $this->order->addItem($this->item);
        $this->order->addItem($this->item2);
        $this->order->setType($this->orderTypeManual);

        $order2->setType($this->orderTypeManual);
        $item = $this->createNewTestItem();
        $item2 = $this->createNewTestItem();
        $order2->addItem($item);
        $order2->addItem($item2);

        $this->addonPrice = new AddonPrice();
        $this->addonPrice->setPrice(123.56);
        $this->addonPrice->setCurrency($this->currency);

        $this->addon = new Addon();
        $this->addon->setProduct($this->product);
        $this->addon->setAddon($this->product2);
        $this->addonPrice->setAddon($this->addon);
        $this->addon->addAddonPrice($this->addonPrice);

        $this->em->persist($this->addon);
        $this->em->persist($this->addonPrice);

        $this->em->persist($item);
        $this->em->persist($item2);

        $this->em->persist($accountAddress);
        $this->em->persist($this->currency);
        $this->em->persist($this->productPrice);
        $this->em->persist($user);
        $this->em->persist($this->orderTypeManual);
        $this->em->persist($this->orderTypeShop);
        $this->em->persist($this->orderTypeAnon);
        $this->em->persist($orderTypeTranslationManual);
        $this->em->persist($orderTypeTranslationShop);
        $this->em->persist($orderTypeTranslationAnon);
        $this->em->persist($this->account);
        $this->em->persist($this->account2);
        $this->em->persist($title);
        $this->em->persist($country);
        $this->em->persist($this->termsOfPayment);
        $this->em->persist($this->termsOfDelivery);
        $this->em->persist($country);
        $this->em->persist($addressType);
        $this->em->persist($this->address);
        $this->em->persist($this->address2);
        $this->em->persist($phoneType);
        $this->em->persist($this->phone);
        $this->em->persist($this->contact);
        $this->em->persist($this->contact2);
        $this->em->persist($this->orderAddressDelivery);
        $this->em->persist($this->orderAddressInvoice);
        $this->em->persist($this->item);
        $this->em->persist($this->item2);
        $this->em->persist($this->product);
        $this->em->persist($this->product2);
        $this->em->persist($this->productTranslation);
        $this->em->persist($productType);
        $this->em->persist($productTypeTranslation);
        $this->em->persist($productStatus);
        $this->em->persist($productStatusTranslation);

        $this->em->flush();
    }

    /**
     * Creates a test order.
     *
     * @return Order
     */
    public function createNewTestOrder()
    {
        // Order
        $order = new Order();
        $order->setNumber('1234');
        $order->setCommission('commission');
        $order->setCostCentre('cost-centre');
        $order->setCustomerName($this->contact->getFullName());
        $order->setCurrencyCode($this->defaultCurrencyCode);
        $order->setTermsOfDelivery($this->termsOfDelivery);
        $order->setTermsOfDeliveryContent($this->termsOfDelivery->getTerms());
        $order->setTermsOfPayment($this->termsOfPayment);
        $order->setTermsOfPaymentContent($this->termsOfPayment->getTerms());
        $order->setCreated(new \DateTime());
        $order->setChanged(new \DateTime());
        $order->setCreator();
        $order->setDesiredDeliveryDate(new \DateTime('2015-01-01'));
        $order->setSessionId('abcd1234');
        $order->setTaxfree(true);
        $order->setCustomerContact($this->contact);
        $order->setCustomerAccount($this->account);
        $order->setStatus($this->orderStatus);
        $order->setBitmaskStatus($this->orderStatus->getId());
        $order->setDeliveryAddress($this->orderAddressDelivery);
        $order->setInvoiceAddress($this->orderAddressInvoice);
        $order->setCreator($this->user);
        $order->setChanger($this->user);
        $order->setResponsibleContact($this->contact2);
        $order->setInternalNote('tiny internal note');

        $this->em->persist($order);

        return $order;
    }

    /**
     * Creates new item for test purpose.
     *
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface
     */
    public function createNewTestItem()
    {
        $item = $this->itemFactory->createEntity();
        $item->setName('Product1');
        $item->setNumber('123');
        $item->setQuantity(2);
        $item->setQuantityUnit('Pcs');
        $item->setUseProductsPrice(true);
        $item->setTax(20);
        $item->setPrice($this->productPrice->getPrice());
        $item->setDiscount(10);
        $item->setDescription('This is a description');
        $item->setWeight(15.8);
        $item->setWidth(5);
        $item->setHeight(6);
        $item->setLength(7);
        $item->setCreated(new DateTime());
        $item->setChanged(new DateTime());
        $item->setProduct($this->product);
        $item->setSupplier($this->account);
        $item->setSupplierName($this->account->getName());

        return $item;
    }

    /**
     * Creates account contact relation.
     *
     * @param AccountInterface $account
     * @param ContactInterface $contact
     * @param bool $isMain
     *
     * @return AccountContact
     */
    protected function createAccountContact(AccountInterface $account, ContactInterface $contact, $isMain = true)
    {
        $accountContact = new AccountContact();
        $accountContact->setAccount($account);
        $accountContact->setContact($contact);
        $accountContact->setMain($isMain);
        $this->contact->addAccountContact($accountContact);

        $this->em->persist($accountContact);

        return $accountContact;
    }
}
