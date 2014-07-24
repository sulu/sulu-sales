<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ItemStatus
 */
class ItemStatus
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $item;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->item = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     * @return ItemStatus
     */
    public function addItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item)
    {
        $this->item[] = $item;
    
        return $this;
    }

    /**
     * Remove item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     */
    public function removeItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item)
    {
        $this->item->removeElement($item);
    }

    /**
     * Get item
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Add translations
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation $translations
     * @return ItemStatus
     */
    public function addTranslation(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation $translations)
    {
        $this->translations[] = $translations;
    
        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation $translations
     */
    public function removeTranslation(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
