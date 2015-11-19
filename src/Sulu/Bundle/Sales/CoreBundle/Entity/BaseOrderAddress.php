<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use Sulu\Bundle\ContactBundle\Entity\Address;

abstract class BaseOrderAddress implements OrderAddressInterface
{
    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $salutation;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $firstName;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $lastName;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $accountName;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $street;

    /**
     * @var string
     */
    private $addition;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $number;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $city;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $zip;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $state;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $country;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $uid;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $email;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $phone;

    /**
     * @var string
     */
    private $phoneMobile;

    /**
     * @var integer
     * @Groups({"Default", "xmlOrder"})
     */
    private $id;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $postboxCity;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $postboxNumber;

    /**
     * @var string
     * @Groups({"Default", "xmlOrder"})
     */
    private $postboxPostcode;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Address
     * @Exclude
     */
    private $contactAddress;

    /**
     * @var string
     */
    private $note;

    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return OrderAddress
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return OrderAddress
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return OrderAddress
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set accountName
     *
     * @param string $accountName
     *
     * @return OrderAddress
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * Get accountName
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return OrderAddress
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return OrderAddress
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set addition
     *
     * @param string $addition
     *
     * @return OrderAddress
     */
    public function setAddition($addition)
    {
        $this->addition = $addition;

        return $this;
    }

    /**
     * Get addition
     *
     * @return string
     */
    public function getAddition()
    {
        return $this->addition;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return OrderAddress
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return OrderAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return OrderAddress
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return OrderAddress
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return OrderAddress
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return OrderAddress
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return OrderAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phoneMobile
     *
     * @param string $phoneMobile
     *
     * @return OrderAddress
     */
    public function setPhoneMobile($phoneMobile)
    {
        $this->phoneMobile = $phoneMobile;

        return $this;
    }

    /**
     * Get phoneMobile
     *
     * @return string
     */
    public function getPhoneMobile()
    {
        return $this->phoneMobile;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set postboxCity
     *
     * @param string $postboxCity
     *
     * @return OrderAddress
     */
    public function setPostboxCity($postboxCity)
    {
        $this->postboxCity = $postboxCity;

        return $this;
    }

    /**
     * Get postboxCity
     *
     * @return string
     */
    public function getPostboxCity()
    {
        return $this->postboxCity;
    }

    /**
     * Set postboxNumber
     *
     * @param string $postboxNumber
     *
     * @return OrderAddress
     */
    public function setPostboxNumber($postboxNumber)
    {
        $this->postboxNumber = $postboxNumber;

        return $this;
    }

    /**
     * Get postboxNumber
     *
     * @return string
     */
    public function getPostboxNumber()
    {
        return $this->postboxNumber;
    }

    /**
     * Set postboxPostcode
     *
     * @param string $postboxPostcode
     *
     * @return OrderAddress
     */
    public function setPostboxPostcode($postboxPostcode)
    {
        $this->postboxPostcode = $postboxPostcode;

        return $this;
    }

    /**
     * Get postboxPostcode
     *
     * @return string
     */
    public function getPostboxPostcode()
    {
        return $this->postboxPostcode;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return OrderAddress
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set contactAddress
     *
     * @param Address $contactAddress
     *
     * @return Item
     */
    public function setContactAddress(Address $contactAddress = null)
    {
        $this->contactAddress = $contactAddress;

        return $this;
    }

    /**
     * Get contactAddress
     *
     * @return Address
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OrderAddress
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Copies address data from one order-address-interface to another
     *
     * @param OrderAddressInterface $from
     * @param OrderAddressInterface $to
     */
    public function copyValuesFromInterface(OrderAddressInterface $from, OrderAddressInterface $to)
    {
        // account
        $to->setAccountName($from->getAccountName());
        $to->setUid($from->getUid());
        // contact
        $to->setTitle($from->getTitle());
        $to->setSalutation($from->getSalutation());
        $to->setFirstName($from->getFirstName());
        $to->setLastName($from->getLastName());
        $to->setEmail($from->getEmail());
        $to->setPhone($from->getPhone());
        $to->setPhoneMobile($from->getPhoneMobile());
        // address
        $to->setStreet($from->getStreet());
        $to->setNumber($from->getNumber());
        $to->setAddition($from->getAddition());
        $to->setZip($from->getZip());
        $to->setCity($from->getCity());
        $to->setState($from->getState());
        $to->setCountry($from->getCountry());
        $to->setContactAddress($from->getContactAddress());
        $to->setNote($from->getNote());
        // postbox
        $to->setPostboxCity($from->getPostboxCity());
        $to->setPostboxNumber($from->getPostboxNumber());
        $to->setPostboxPostcode($from->getPostboxPostcode());
    }

    /**
     * Converts a BaseOrderAddress
     */
    public function toArray()
    {
        return array(
            // account
            'accountName' => $this->getAccountName(),
            'uid' => $this->getUid(),
            // contact
            'title' => $this->getTitle(),
            'salutation' => $this->getSalutation(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'phoneMobile' => $this->getPhoneMobile(),
            // address
            'street' => $this->getStreet(),
            'number' => $this->getNumber(),
            'addition' => $this->getAddition(),
            'zip' => $this->getZip(),
            'city' => $this->getCity(),
            'state' => $this->getState(),
            'country' => $this->getCountry(),
            'note' => $this->getNote(),
            'contactAddress' => $this->getContactAddress() ? array(
                'id' => $this->getContactAddress()->getId()
            ) : null,
            // postbox
            'postboxCity' => $this->getPostboxCity(),
            'postboxNumber' => $this->getPostboxNumber(),
            'postboxPostcode' => $this->getPostboxPostcode(),
        );
    }
}
