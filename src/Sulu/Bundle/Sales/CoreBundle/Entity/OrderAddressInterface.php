<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\Address;

interface OrderAddressInterface
{
    /**
     * @param string $salutation
     *
     * @return $this
     */
    public function setSalutation($salutation);

    /**
     * @return string
     */
    public function getSalutation();

    /**
     * @return int
     */
    public function getFormOfAddress();

    /**
     * @param int $formOfAddress
     *
     * @return $this
     */
    public function setFormOfAddress($formOfAddress);

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @param string $accountName
     *
     * @return $this
     */
    public function setAccountName($accountName);

    /**
     * @return string
     */
    public function getAccountName();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street);

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @param string $addition
     *
     * @return $this
     */
    public function setAddition($addition);

    /**
     * @return string
     */
    public function getAddition();

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number);

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $zip
     *
     * @return $this
     */
    public function setZip($zip);

    /**
     * @return string
     */
    public function getZip();

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state);

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $uid
     *
     * @return $this
     */
    public function setUid($uid);

    /**
     * @return string
     */
    public function getUid();

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone);

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @param string $phoneMobile
     *
     * @return $this
     */
    public function setPhoneMobile($phoneMobile);

    /**
     * @return string
     */
    public function getPhoneMobile();

    /**
     * @return integer
     */
    public function setId($id);

    /**
     * @return integer
     */
    public function getId();

    /**
     * @param string $postboxCity
     *
     * @return $this
     */
    public function setPostboxCity($postboxCity);

    /**
     * @return string
     */
    public function getPostboxCity();

    /**
     * @param string $postboxNumber
     *
     * @return $this
     */
    public function setPostboxNumber($postboxNumber);

    /**
     * @return string
     */
    public function getPostboxNumber();

    /**
     * @param string $postboxPostcode
     *
     * @return self
     */
    public function setPostboxPostcode($postboxPostcode);

    /**
     * @return string
     */
    public function getPostboxPostcode();

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * @param Address $contactAddress
     *
     * @return $this
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
     * @return $this
     */
    public function setNote($note);

    /**
     * Get note
     *
     * @return string
     */
    public function getNote();
}
