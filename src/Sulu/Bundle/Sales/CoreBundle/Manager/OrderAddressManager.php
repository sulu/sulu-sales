<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressRepository;
use Sulu\Bundle\Sales\CoreBundle\Exceptions\MissingAttributeException;

class OrderAddressManager
{
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $orderAddressEntityName = 'SuluSalesCoreBundle:OrderAddress';
    protected static $orderAddressEntity = 'Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var OrderAddressRepository
     */
    protected $orderAddressRepository;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em,
        OrderAddressRepository $orderAddressRepository
    ) {
        $this->em = $em;
        $this->orderAddressRepository = $orderAddressRepository;
    }

    /**
     * @param int $id
     *
     * @return null|OrderAddressInterface
     */
    public function findById($id)
    {
        return $this->orderAddressRepository->find($id);
    }

    /**
     * Sets an order address based on given data
     *
     * @param OrderAddressInterface $orderAddress
     * @param array $addressData
     * @param ContactInterface $contact
     * @param Account|null $account
     *
     * @throws OrderDependencyNotFoundException
     */
    public function setOrderAddress($orderAddress, $addressData, $contact = null, $account = null)
    {
        // Check if address with id can be found.
        $contactData = $this->getContactData($addressData, $contact);
        // Add contact data.
        if ($contactData) {
            $orderAddress->setFirstName($contactData['firstName']);
            $orderAddress->setLastName($contactData['lastName']);
            if (isset($contactData['title'])) {
                $orderAddress->setTitle($contactData['title']);
            }
            if (isset($contactData['salutation'])) {
                $orderAddress->setSalutation($contactData['salutation']);
            }
        }

        // Add account data.
        $orderAddress->setAccountName(null);
        $orderAddress->setUid(null);

        if ($account) {
            $orderAddress->setAccountName($account->getName());
            $orderAddress->setUid($account->getUid());
        }

        // TODO: add phone

        $this->setAddressDataForOrder($orderAddress, $addressData);
    }

    /**
     * Sets an order-address by data provided by contact-address.
     * If order-address does not exist a new one is created.
     *
     * @param int $addressId
     * @param null|ContactInterface $contact
     * @param null|Account $account
     * @param null|OrderAddressInterface $orderAddress
     *
     * @throws EntityNotFoundException
     *
     * @return OrderAddressInterface
     */
    public function getAndSetOrderAddressByContactAddressId(
        $addressId,
        $contact = null,
        $account = null,
        $orderAddress = null
    ) {
        $address = $this->em->getRepository(static::$addressEntityName)->find($addressId);

        if (!$address) {
            throw new EntityNotFoundException(static::$addressEntityName, $addressId);
        }

        return $this->getAndSetOrderAddressByContactAddress($address, $contact, $account, $orderAddress);
    }

    /**
     * Sets an order-address by data provided by contact-address.
     * If order-address does not exist a new one is created.
     *
     * @param Address $address
     * @param null|ContactInterface $contact
     * @param null|Account $account
     * @param null|OrderAddressInterface $orderAddress
     *
     * @return OrderAddressInterface
     */
    public function getAndSetOrderAddressByContactAddress(
        Address $address,
        $contact = null,
        $account = null,
        $orderAddress = null
    ) {
        if (!$orderAddress) {
            $orderAddress = new static::$orderAddressEntity;
        }
        $orderAddress->setStreet($address->getStreet());
        $orderAddress->setNumber($address->getNumber());
        $orderAddress->setAddition($address->getAddition());
        $orderAddress->setCity($address->getCity());
        $orderAddress->setZip($address->getZip());
        $orderAddress->setState($address->getState());
        $orderAddress->setCountry($address->getCountry()->getName());

        $orderAddress->setPostboxCity($address->getPostboxCity());
        $orderAddress->setPostboxPostcode($address->getPostboxPostcode());
        $orderAddress->setPostboxNumber($address->getPostboxNumber());

        $orderAddress->setContactAddress($address);
        $orderAddress->setNote($address->getNote());

        // add account data
        if ($account) {
            $orderAddress->setAccountName($account->getName());
            $orderAddress->setUid($account->getUid());
        }

        if ($contact) {
            if ($contact->getTitle()) {
                $orderAddress->setTitle($contact->getTitle()->getTitle());
            }
            $orderAddress->setSalutation($contact->getSalutation());
            $orderAddress->setFirstName($contact->getFirstName());
            $orderAddress->setLastName($contact->getLastName());
            $orderAddress->setEmail($contact->getMainEmail());
            $orderAddress->setPhone($contact->getMainPhone());
        }

        return $orderAddress;
    }

    /**
     * Sets contact data to address data.
     *
     * @param array $addressData
     * @param ContactInterface $contact
     */
    public function mergeContactIntoAddressData(array &$addressData, ContactInterface $contact)
    {
        $addressData['firstName'] = $contact->getFirstName();
        $addressData['lastName'] = $contact->getLastName();
        $addressData['fullName'] = $contact->getFullName();
        $addressData['salutation'] = $contact->getFormOfAddress();
        if ($contact->getTitle() !== null) {
            $addressData['title'] = $contact->getTitle()->getTitle();
        }
    }

    /**
     * Copies address data to order address.
     *
     * @param OrderAddressInterface $orderAddress
     * @param array $addressData
     */
    private function setAddressDataForOrder(&$orderAddress, $addressData)
    {
        $orderAddress->setStreet($this->getProperty($addressData, 'street', ''));
        $orderAddress->setNumber($this->getProperty($addressData, 'number', ''));
        $orderAddress->setAddition($this->getProperty($addressData, 'addition', ''));
        $orderAddress->setCity($this->getProperty($addressData, 'city', ''));
        $orderAddress->setZip($this->getProperty($addressData, 'zip', ''));
        $orderAddress->setState($this->getProperty($addressData, 'state', ''));

        $countryName = $this->getProperty($addressData, 'country', '');
        // Get country name if country is an array.
        if (is_array($countryName)) {
            $countryName = '';
            if (isset($countryName['name'])) {
                $countryName = $countryName['name'];
            }
        }
        $orderAddress->setCountry($countryName);
        $orderAddress->setEmail($this->getProperty($addressData, 'email', ''));
        $orderAddress->setPhone($this->getProperty($addressData, 'phone', ''));
        $orderAddress->setNote($this->getProperty($addressData, 'note', ''));

        $orderAddress->setPostboxCity($this->getProperty($addressData, 'postboxCity', ''));
        $orderAddress->setPostboxPostcode($this->getProperty($addressData, 'postboxPostcode', ''));
        $orderAddress->setPostboxNumber($this->getProperty($addressData, 'postboxNumber', ''));

        $address = null;
        if ($this->getProperty($addressData, 'address')) {
            $address = $this->getProperty($addressData, 'address');
        } elseif ($this->getProperty($addressData, 'contactAddress')) {
            $address = $this->getProperty($addressData, 'contactAddress');
        }
        if ($address) {
            $this->getAndSetOrderAddressByContactAddressId($address, null, null, $orderAddress);
        }
    }

    /**
     * Returns contact data as an array.
     * Either by provided address or contact.
     *
     * @param array $addressData
     * @param ContactInterface $contact
     *
     * @throws MissingAttributeException
     *
     * @return array
     */
    public function getContactData($addressData, ContactInterface $contact = null)
    {
        $result = array();

        if ($contact) {
            $this->mergeContactIntoAddressData($result, $contact);
        } elseif ($addressData && isset($addressData['firstName']) && isset($addressData['lastName'])) {
            $result['firstName'] = $addressData['firstName'];
            $result['lastName'] = $addressData['lastName'];
            $result['fullName'] = $result['firstName'] . ' ' . $result['lastName'];
            if (isset($addressData['title'])) {
                $result['title'] = $addressData['title'];
            }
            if (isset($addressData['salutation'])) {
                $result['salutation'] = $addressData['salutation'];
            }
        } else {
            throw new MissingAttributeException('firstName, lastName or contact');
        }

        return $result;
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
}
