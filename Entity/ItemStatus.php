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
    private $itemStatusTranslations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->item = new \Doctrine\Common\Collections\ArrayCollection();
        $this->itemStatusTranslations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add itemStatusTranslations
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslations $itemStatusTranslations
     * @return ItemStatus
     */
    public function addItemStatusTranslation(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslations $itemStatusTranslations)
    {
        $this->itemStatusTranslations[] = $itemStatusTranslations;
    
        return $this;
    }

    /**
     * Remove itemStatusTranslations
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslations $itemStatusTranslations
     */
    public function removeItemStatusTranslation(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslations $itemStatusTranslations)
    {
        $this->itemStatusTranslations->removeElement($itemStatusTranslations);
    }

    /**
     * Get itemStatusTranslations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItemStatusTranslations()
    {
        return $this->itemStatusTranslations;
    }
}
