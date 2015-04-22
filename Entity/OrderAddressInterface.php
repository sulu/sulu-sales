<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\Address;

interface OrderAddressInterface
{
    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return OrderAddress
     */
    public function setSalutation($salutation);

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation();

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return OrderAddress
     */
    public function setFirstName($firstName);

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return OrderAddress
     */
    public function setLastName($lastName);

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set accountName
     *
     * @param string $accountName
     *
     * @return OrderAddress
     */
    public function setAccountName($accountName);

    /**
     * Get accountName
     *
     * @return string
     */
    public function getAccountName();

    /**
     * Set title
     *
     * @param string $title
     *
     * @return OrderAddress
     */
    public function setTitle($title);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set street
     *
     * @param string $street
     *
     * @return OrderAddress
     */
    public function setStreet($street);

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Set addition
     *
     * @param string $addition
     *
     * @return OrderAddress
     */
    public function setAddition($addition);

    /**
     * Get addition
     *
     * @return string
     */
    public function getAddition();

    /**
     * Set number
     *
     * @param string $number
     *
     * @return OrderAddress
     */
    public function setNumber($number);

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber();

    /**
     * Set city
     *
     * @param string $city
     *
     * @return OrderAddress
     */
    public function setCity($city);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return OrderAddress
     */
    public function setZip($zip);

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip();

    /**
     * Set state
     *
     * @param string $state
     *
     * @return OrderAddress
     */
    public function setState($state);

    /**
     * Get state
     *
     * @return string
     */
    public function getState();

    /**
     * Set country
     *
     * @param string $country
     *
     * @return OrderAddress
     */
    public function setCountry($country);

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return OrderAddress
     */
    public function setUid($uid);

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid();

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return OrderAddress
     */
    public function setPhone($phone);

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set phoneMobile
     *
     * @param string $phoneMobile
     *
     * @return OrderAddress
     */
    public function setPhoneMobile($phoneMobile);

    /**
     * Get phoneMobile
     *
     * @return string
     */
    public function getPhoneMobile();

    /**
     * Get id
     *
     * @return integer
     */
    public function setId($id);

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set postboxCity
     *
     * @param string $postboxCity
     *
     * @return OrderAddress
     */
    public function setPostboxCity($postboxCity);

    /**
     * Get postboxCity
     *
     * @return string
     */
    public function getPostboxCity();

    /**
     * Set postboxNumber
     *
     * @param string $postboxNumber
     *
     * @return OrderAddress
     */
    public function setPostboxNumber($postboxNumber);

    /**
     * Get postboxNumber
     *
     * @return string
     */
    public function getPostboxNumber();

    /**
     * Set postboxPostcode
     *
     * @param string $postboxPostcode
     *
     * @return OrderAddress
     */
    public function setPostboxPostcode($postboxPostcode);

    /**
     * Get postboxPostcode
     *
     * @return string
     */
    public function getPostboxPostcode();

    /**
     * Set email
     *
     * @param string $email
     *
     * @return OrderAddress
     */
    public function setEmail($email);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set contactAddress
     *
     * @param Address $contactAddress
     *
     * @return Item
     */
    public function setContactAddress(Address $contactAddress = null);

    /**
     * Get contactAddress
     *
     * @return Address
     */
    public function getContactAddress();

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OrderAddress
     */
    public function setNote($note);

    /**
     * Get note
     *
     * @return string
     */
    public function getNote();
}
