<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderAddress
 */
class OrderAddress
{
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
    private $plz;

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
    private $box;

    /**
     * @var string
     */
    private $uid;

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
     * Set firstName
     *
     * @param string $firstName
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
     * Set plz
     *
     * @param string $plz
     * @return OrderAddress
     */
    public function setPlz($plz)
    {
        $this->plz = $plz;
    
        return $this;
    }

    /**
     * Get plz
     *
     * @return string 
     */
    public function getPlz()
    {
        return $this->plz;
    }

    /**
     * Set state
     *
     * @param string $state
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
     * Set box
     *
     * @param string $box
     * @return OrderAddress
     */
    public function setBox($box)
    {
        $this->box = $box;
    
        return $this;
    }

    /**
     * Get box
     *
     * @return string 
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * Set uid
     *
     * @param string $uid
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
    public function getId()
    {
        return $this->id;
    }
}
