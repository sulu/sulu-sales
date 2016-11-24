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
     * @var int
     * @Groups({"Default", "xmlOrder"})
     */
    private $formOfAddress;

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
     * @return $this
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @return int
     */
    public function getFormOfAddress()
    {
        return $this->formOfAddress;
    }

    /**
     * @param int $formOfAddress
     *
     * @return $this
     */
    public function setFormOfAddress($formOfAddress)
    {
        $this->formOfAddress = $formOfAddress;

        return $this;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return $this
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
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $accountName
     *
     * @return $this
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $postboxCity
     *
     * @return $this
     */
    public function setPostboxCity($postboxCity)
    {
        $this->postboxCity = $postboxCity;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostboxCity()
    {
        return $this->postboxCity;
    }

    /**
     * @param string $postboxNumber
     *
     * @return $this
     */
    public function setPostboxNumber($postboxNumber)
    {
        $this->postboxNumber = $postboxNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostboxNumber()
    {
        return $this->postboxNumber;
    }

    /**
     * @param string $postboxPostcode
     *
     * @return $this
     */
    public function setPostboxPostcode($postboxPostcode)
    {
        $this->postboxPostcode = $postboxPostcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostboxPostcode()
    {
        return $this->postboxPostcode;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param Address $contactAddress
     *
     * @return $this
     */
    public function setContactAddress(Address $contactAddress = null)
    {
        $this->contactAddress = $contactAddress;

        return $this;
    }

    /**
     * @return Address
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * @param string $note
     *
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Copies address data from one order-address-interface to another.
     *
     * @param OrderAddressInterface $from
     * @param OrderAddressInterface $to
     */
    public function copyValuesFromInterface(OrderAddressInterface $from, OrderAddressInterface $to)
    {
        // Account.
        $to->setAccountName($from->getAccountName());
        $to->setUid($from->getUid());
        // Contact.
        $to->setTitle($from->getTitle());
        $to->setSalutation($from->getSalutation());
        $to->setFormOfAddress($from->getFormOfAddress());
        $to->setFirstName($from->getFirstName());
        $to->setLastName($from->getLastName());
        $to->setEmail($from->getEmail());
        $to->setPhone($from->getPhone());
        $to->setPhoneMobile($from->getPhoneMobile());
        // Address.
        $to->setStreet($from->getStreet());
        $to->setNumber($from->getNumber());
        $to->setAddition($from->getAddition());
        $to->setZip($from->getZip());
        $to->setCity($from->getCity());
        $to->setState($from->getState());
        $to->setCountry($from->getCountry());
        $to->setContactAddress($from->getContactAddress());
        $to->setNote($from->getNote());
        // Postbox.
        $to->setPostboxCity($from->getPostboxCity());
        $to->setPostboxNumber($from->getPostboxNumber());
        $to->setPostboxPostcode($from->getPostboxPostcode());
    }

    /**
     * Converts a BaseOrderAddress.
     */
    public function toArray()
    {
        return array(
            // Account.
            'accountName' => $this->getAccountName(),
            'uid' => $this->getUid(),
            // Contact.
            'title' => $this->getTitle(),
            'salutation' => $this->getSalutation(),
            'formOfAddress' => $this->getFormOfAddress(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'phoneMobile' => $this->getPhoneMobile(),
            // Address.
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
            // Postbox.
            'postboxCity' => $this->getPostboxCity(),
            'postboxNumber' => $this->getPostboxNumber(),
            'postboxPostcode' => $this->getPostboxPostcode(),
        );
    }
}
