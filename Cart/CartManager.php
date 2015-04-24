<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Cart;

use Doctrine\Common\Persistence\ObjectManager;

use Sulu\Bundle\Sales\CoreBundle\Manager\BaseSalesManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\GroupedItemsPriceCalculatorInterface;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderFactoryInterface;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderPdfManager;
use Sulu\Component\Persistence\RelationTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartManager extends BaseSalesManager
{
    use RelationTrait;

    /**
     * TODO: replace by config
     *
     * defines when a cart expires
     */
    const EXPIRY_MONTHS = 2;

    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $contactEntityName = 'SuluContactBundle:Contact';
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $accountEntityName = 'SuluContactBundle:Account';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderTypeEntityName = 'SuluSalesOrderBundle:OrderType';
    protected static $orderTypeTranslationEntityName = 'SuluSalesOrderBundle:OrderTypeTranslation';
    protected static $orderAddressEntityName = 'SuluSalesCoreBundle:OrderAddress';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactBundle:TermsOfPayment';
    protected static $statusClass = 'Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus';

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var GroupedItemsPriceCalculatorInterface
     */
    private $priceCalculation;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @var
     */
    private $accountManager;

    /**
     * @var OrderPdfManager
     */
    private $pdfManager;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $mailerFrom;

    /**
     * @var OrderFactoryInterface
     */
    protected $orderFactory;

    /**
     * @param ObjectManager $em
     * @param SessionInterface $session
     * @param OrderRepository $orderRepository
     * @param OrderManager $orderManager
     * @param GroupedItemsPriceCalculatorInterface $priceCalculation
     * @param string $defaultCurrency
     * @param AccountManager $accountManager
     * @param \Twig_Environment $twig
     * @param OrderPdfManager $pdfManager
     * @param \Swift_Mailer $mailer
     * @param string $mailerFrom
     * @param OrderFactoryInterface $orderFactory
     */
    public function __construct(
        ObjectManager $em,
        SessionInterface $session,
        OrderRepository $orderRepository,
        OrderManager $orderManager,
        GroupedItemsPriceCalculatorInterface $priceCalculation,
        $defaultCurrency,
        $accountManager,
        \Twig_Environment $twig,
        OrderPdfManager $pdfManager,
        \Swift_Mailer $mailer,
        $mailerFrom,
        OrderFactoryInterface $orderFactory
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderManager = $orderManager;
        $this->priceCalculation = $priceCalculation;
        $this->defaultCurrency = $defaultCurrency;
        $this->accountManager = $accountManager;
        $this->twig = $twig;
        $this->pdfManager = $pdfManager;
        $this->mailer = $mailer;
        $this->mailerFrom = $mailerFrom;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param $user
     * @param null|string $locale
     * @param null|string $currency
     * @param bool $persistEmptyCart Define if an empty cart should be persisted
     * @param bool $updatePrices Defines if prices should be updated
     *
     * @return null|ApiOrder
     */
    public function getUserCart(
        $user = null,
        $locale = null,
        $currency = null,
        $persistEmptyCart = false,
        $updatePrices = false
    ) {
        // cart by session ID
        if (!$user) {
            // TODO: get correct locale
            $locale = 'de';
            $cartArray = $this->findCartBySessionId();
        } else {
            // TODO: check if cart for this sessionId exists and assign it to user

            // default locale from user
            $locale = $locale ?: $user->getLocale();
            // get carts
            $cartArray = $this->findCartByUser($locale, $user);
        }

        // cleanup cart array: remove duplicates and expired carts
        $this->cleanupCartArray($cartArray);

        // check if cart exists
        if ($cartArray && count($cartArray) > 0) {
            // multiple carts found, do a cleanup
            $cart = $cartArray[0];
        } else {
            // user has no cart - return empty one
            $cart = $this->createEmptyCart($user, $persistEmptyCart);
        }

        $apiOrder = $this->orderFactory->createApiEntity($cart, $locale);

        $this->orderManager->updateApiEntity($apiOrder, $locale);

        if ($updatePrices) {
            $this->updateCartPrices($apiOrder->getItems());
        }

        return $apiOrder;
    }

    /**
     * Updates changed prices
     *
     * @param $items
     *
     * @return bool
     */
    public function updateCartPrices($items)
    {
        // set prices to changed
        $hasChanged = $this->priceCalculation->setPricesOfChanged($items);
        if ($hasChanged) {
            $this->em->flush();
        }

        return $hasChanged;
    }

    /**
     * Updates the cart
     *
     * @param array $data
     * @param $user
     * @param $locale
     *
     * @return null|Order
     * @throws \Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException
     * @throws \Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException
     */
    public function updateCart($data, $user, $locale)
    {
        $cart = $this->getUserCart($user, $locale);
        $userId = $user ? $user->getId() : null;
        $this->orderManager->save($data, $locale, $userId, $cart->getId());

        return $cart;
    }

    /**
     * Submits an order
     *
     * @param $user
     * @param $locale
     * @param bool $orderWasSubmitted
     *
     * @return null|ApiOrder
     * @throws OrderException
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    public function submit($user, $locale, &$orderWasSubmitted = true)
    {
        $orderWasSubmitted = true;

        $cart = $this->getUserCart($user, $locale, null, false, true);
        if ($cart->hasChangedPrices()) {
            $orderWasSubmitted = false;

            return $cart;
        } else {
            if (count($cart->getItems()) < 1) {
                throw new OrderException('Empty Cart');
            }

            // change status of order to confirmed
            $this->orderManager->convertStatus($cart, OrderStatus::STATUS_CONFIRMED);

            // send confirmation email
            $this->sendConfirmationEmail($user->getContact()->getMainEmail(), $cart);
        }

        // flush on success
        $this->em->flush();

        return $this->getUserCart($user, $locale);
    }

    /**
     * Finds cart by session-id
     *
     * @return array
     */
    private function findCartBySessionId()
    {
        $sessionId = $this->session->getId();
        $cartArray = $this->orderRepository->findBy(
            array(
                'sessionId' => $sessionId,
                'status' => OrderStatus::STATUS_IN_CART
            ),
            array(
                'created' => 'DESC'
            )
        );

        return $cartArray;
    }

    /**
     * Finds cart by locale and user
     *
     * @param $locale
     * @param $user
     *
     * @return array|null
     */
    private function findCartByUser($locale, $user)
    {
        $cartArray = $this->orderRepository->findByStatusIdAndUser(
            $locale,
            OrderStatus::STATUS_IN_CART,
            $user
        );

        return $cartArray;
    }

    /**
     * removes all elements from database but the first
     *
     * @param $cartArray
     */
    private function cleanupCartArray(&$cartArray)
    {
        if ($cartArray && count($cartArray) > 0) {
            // handle cartArray count is > 1
            foreach ($cartArray as $index => $cart) {
                // delete expired carts
                if ($cart->getChanged()->getTimestamp() < strtotime(static::EXPIRY_MONTHS . ' months ago')) {
                    $this->em->remove($cart);
                    continue;
                }

                // dont delete first element, since this is the current cart
                if ($index === 0) {
                    continue;
                }
                // remove duplicated carts
                $this->em->remove($cart);
            }
        }
    }

    /**
     * adds a product to cart
     *
     * @param $data
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     */
    public function addProduct($data, $user = null, $locale = null)
    {
        //TODO: locale
        // get cart
        $cart = $this->getUserCart($user, $locale, null, true);
        // define user-id
        $userId = $user ? $user->getId() : null;
        $this->orderManager->addItem($data, $locale, $userId, $cart);

        $this->orderManager->updateApiEntity($cart, $locale);

        return $cart;
    }

    /**
     * @param $itemId
     * @param $data
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     * @throws ItemNotFoundException
     */
    public function updateItem($itemId, $data, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);
        $userId = $user ? $user->getId() : null;

        $item = $this->orderManager->getOrderItemById($itemId, $cart->getEntity());

        $this->orderManager->updateItem($item, $data, $locale, $userId);

        $this->orderManager->updateApiEntity($cart, $locale);

        return $cart;
    }

    /**
     * patches an item in cart
     *
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     */
    public function removeItem($itemId, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);

        $item = $this->orderManager->getOrderItemById($itemId, $cart->getEntity(), $hasMultiple);

        $this->orderManager->removeItem($item, $cart->getEntity(), !$hasMultiple);

        $this->orderManager->updateApiEntity($cart, $locale);

        return $cart;
    }

    /**
     * Function creates an empty cart
     * this means an order with status 'in_cart' is created and all necessary data is set
     *
     * @param $user
     * @param $persist
     *
     * @return Order
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    protected function createEmptyCart($user, $persist, $currency = null)
    {
        $cart = new Order();
        $cart->setCreator($user);
        $cart->setChanger($user);
        $cart->setCreated(new \DateTime());
        $cart->setChanged(new \DateTime());

        // set currency - if not defined use default
        $currency = $currency ?: $this->defaultCurrency;
        $cart->setCurrencyCode($currency);

        // get address from contact and account
        $contact = $user->getContact();
        $account = $contact->getMainAccount();
        $cart->setCustomerContact($contact);
        $cart->setCustomerAccount($account);

        /** Account $account */
        if ($account && $account->getResponsiblePerson()) {
            $cart->setResponsibleContact($account->getResponsiblePerson());
        }

        $addressSource = $contact;
        if ($account) {
            $addressSource = $account;
        }
        // get billing address
        $invoiceOrderAddress = null;
        $invoiceAddress = $this->accountManager->getBillingAddress($addressSource, true);
        if ($invoiceAddress) {
            // convert to order-address
            $invoiceOrderAddress = $this->orderManager->getOrderAddressByContactAddress($invoiceAddress, $contact, $account);
            $cart->setInvoiceAddress($invoiceOrderAddress);
        }
        $deliveryOrderAddress = null;
        $deliveryAddress = $this->accountManager->getDeliveryAddress($addressSource, true);
        if ($deliveryAddress) {
            // convert to order-address
            $deliveryOrderAddress = $this->orderManager->getOrderAddressByContactAddress($deliveryAddress, $contact, $account);
            $cart->setDeliveryAddress($deliveryOrderAddress);
        }

        // TODO: anonymous order
        if ($user) {
            $name = $user->getContact()->getFullName();
        } else {
            $name = 'Anonymous';
        }
        $cart->setCustomerName($name);

        $this->orderManager->convertStatus($cart, OrderStatus::STATUS_IN_CART, false, $persist);

        if ($persist) {
            $this->em->persist($cart);
            if ($invoiceOrderAddress) {
                $this->em->persist($invoiceOrderAddress);
            }
            if ($deliveryOrderAddress) {
                $this->em->persist($deliveryOrderAddress);
            }
        }

        return $cart;
    }

    /**
     * returns array containing number of items and total-price
     * array('totalItems', 'totalPrice')
     *
     * @param $user
     * @param $locale
     *
     * @return array
     */
    public function getNumberItemsAndTotalPrice($user, $locale)
    {
        $cart = $this->getUserCart($user, $locale);

        return array(
            'totalItems' => count($cart->getItems()),
            'totalPrice' => $cart->getTotalNetPrice(),
            'totalPriceFormatted' => $cart->getTotalNetPriceFormatted(),
            'currency' => $cart->getCurrencyCode()
        );
    }

    /**
     * @param $recipient
     * @param $apiOrder
     *
     * @return bool
     */
    public function sendConfirmationEmail($recipient, $apiOrder)
    {
        $tmplData = array(
            'order' => $apiOrder,
            'contact' => $apiOrder->getEntity()->getCustomerContact()
        );

        $template = $this->twig->loadTemplate('SuluSalesOrderBundle:Emails:order.confirmation.twig');
        $subject = $template->renderBlock('subject', $tmplData);

        $emailBodyText = $template->renderBlock('body_text', $tmplData);
        $emailBodyHtml = $template->renderBlock('body_html', $tmplData);

        $pdf = $this->pdfManager->createOrderConfirmation($apiOrder);
        $pdfFileName = $this->pdfManager->getPdfName($apiOrder);

        if ($recipient) {
            // now send mail
            $attachment = \Swift_Attachment::newInstance()
                ->setFilename($pdfFileName)
                ->setContentType('application/pdf')
                ->setBody($pdf);

            /** @var \Swift_Message $message */
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->mailerFrom)
                ->setTo($recipient)
                ->setBody($emailBodyText, 'text/plain')
                ->addPart($emailBodyHtml, 'text/html')
                ->attach($attachment);

            return $this->mailer->send($message);
        }

        return false;
    }
}
