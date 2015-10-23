<?php

namespace Sulu\Bundle\Sales\CoreBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress as Entity;

use JMS\Serializer\Annotation\Groups;
use Sulu\Component\Rest\ApiWrapper;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Defines the type of an order
 * @package Sulu\Bundle\Sales\CoreBundle\Api
 */
class OrderAddress extends ApiWrapper
{
    /**
     * @param Entity $entity
     */
    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

    /**
     * Returns the id
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     * @Groups({"Default","cart"})
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return OrderAddress
     */
    public function setSalutation($salutation)
    {
        $this->entity->setSalutation($salutation);

        return $this;
    }

    /**
     * Get salutation
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("salutation")
     * @Groups({"Default","cart"})
     */
    public function getSalutation()
    {
        return $this->entity->getSalutation();
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
        $this->entity->setFirstName($firstName);

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("firstName")
     * @Groups({"Default","cart"})
     */
    public function getFirstName()
    {
        return $this->entity->getFirstName();
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
        $this->entity->setLastName($lastName);

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("lastName")
     * @Groups({"Default","cart"})
     */
    public function getLastName()
    {
        return $this->entity->getLastName();
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
        $this->entity->setAccountName($accountName);

        return $this;
    }

    /**
     * Get accountName
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("accountName")
     * @Groups({"Default","cart"})
     */
    public function getAccountName()
    {
        return $this->entity->getAccountName();
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
        $this->entity->setTitle($title);

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("title")
     * @Groups({"Default","cart"})
     */
    public function getTitle()
    {
        return $this->entity->getTitle();
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
        $this->entity->setStreet($street);

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("street")
     * @Groups({"Default","cart"})
     */
    public function getStreet()
    {
        return $this->entity->getStreet();
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
        $this->entity->setAddition($addition);

        return $this;
    }

    /**
     * Get addition
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("addition")
     * @Groups({"Default","cart"})
     */
    public function getAddition()
    {
        return $this->entity->getAddition();
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
        $this->entity->setNumber($number);

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("number")
     * @Groups({"Default","cart"})
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
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
        $this->entity->setCity($city);

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("city")
     * @Groups({"Default","cart"})
     */
    public function getCity()
    {
        return $this->entity->getCity();
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
        $this->entity->setZip($zip);

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("zip")
     * @Groups({"Default","cart"})
     */
    public function getZip()
    {
        return $this->entity->getZip();
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
        $this->entity->setState($state);

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("state")
     * @Groups({"Default","cart"})
     */
    public function getState()
    {
        return $this->entity->getState();
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
        $this->entity->setCountry($country);

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("country")
     * @Groups({"Default","cart"})
     */
    public function getCountry()
    {
        return $this->entity->getCountry();
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
        $this->entity->setUid($uid);

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("uid")
     * @Groups({"Default","cart"})
     */
    public function getUid()
    {
        return $this->entity->getUid();
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
        $this->entity->setPhone($phone);

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("phone")
     * @Groups({"Default","cart"})
     */
    public function getPhone()
    {
        return $this->entity->getPhone();
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
        $this->entity->setPhoneMobile($phoneMobile);

        return $this;
    }

    /**
     * Get phoneMobile
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("phoneMobile")
     * @Groups({"Default","cart"})
     */
    public function getPhoneMobile()
    {
        return $this->entity->getPhoneMobile();
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
        $this->entity->setNote($note);

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("note")
     * @Groups({"Default","cart"})
     */
    public function getNote()
    {
        return $this->entity->getNote();
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
        $this->entity->setPostboxCity($postboxCity);

        return $this;
    }

    /**
     * Get postboxCity
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("postboxCity")
     * @Groups({"Default","cart"})
     */
    public function getPostboxCity()
    {
        return $this->entity->getPostboxCity();
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
        $this->entity->setPostboxNumber($postboxNumber);

        return $this;
    }

    /**
     * Get postboxNumber
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("postboxNumber")
     * @Groups({"Default","cart"})
     */
    public function getPostboxNumber()
    {
        return $this->entity->getPostboxNumber();
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
        $this->entity->setPostboxPostcode($postboxPostcode);

        return $this;
    }

    /**
     * Get postboxPostcode
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("postboxCode")
     * @Groups({"Default","cart"})
     */
    public function getPostboxPostcode()
    {
        return $this->entity->getPostboxPostcode();
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
        $this->entity->setEmail($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("email")
     * @Groups({"Default","cart"})
     */
    public function getEmail()
    {
        return $this->entity->getEmail();
    }

    /**
     * Set contactAddress
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\Address $contactAddress
     *
     * @return Item
     */
    public function setContactAddress(\Sulu\Bundle\ContactBundle\Entity\Address $contactAddress = null)
    {
        $this->entity->setContactAddress($contactAddress);

        return $this;
    }

    /**
     * Get contactAddress
     *
     * @return null|\Sulu\Bundle\ContactBundle\Entity\Address $contactAddress
     */
    public function getContactAddress()
    {
        if ($this->entity->getContactAddress()) {
            return $this->entity->getContactAddress();
        }
        return null;
    }

    /**
     * Get contactAddress id
     *
     * @return int|null
     * @VirtualProperty
     * @SerializedName("contactAddressId")
     * @Groups({"Default","cart"})
     */
    public function getContactAddressId()
    {
        if ($this->entity->getContactAddress()) {
            return $this->entity->getContactAddress()->getId();
        }
        return null;
    }
}
