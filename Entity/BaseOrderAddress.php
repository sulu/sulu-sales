<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\Address;

abstract class BaseOrderAddress implements OrderAddressInterface
{
    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $accountName;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $addition;

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $phoneMobile;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $postboxCity;

    /**
     * @var string
     */
    private $postboxNumber;

    /**
     * @var string
     */
    private $postboxPostcode;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Address
     */
    private $contactAddress;

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
}
