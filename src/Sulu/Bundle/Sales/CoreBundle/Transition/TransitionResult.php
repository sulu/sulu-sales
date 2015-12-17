<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Sulu\Bundle\Sales\CoreBundle\Entity\Transition;

class TransitionResult implements TransitionResultInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $destinationId;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var string
     */
    protected $pdfUrl;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var Transition
     */
    protected $transition;

    /**
     * @var string
     */
    protected $translationkey = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->pdfUrl;
    }

    /**
     * @param string $pdfUrl
     *
     * @return self
     */
    public function setPdfUrl($pdfUrl)
    {
        $this->pdfUrl = $pdfUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param Transition $transition
     *
     * @return self
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;

        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationkey()
    {
        return $this->translationkey;
    }

    /**
     * @param string $translationkey
     *
     * @return self
     */
    public function setTranslationkey($translationkey)
    {
        $this->translationkey = $translationkey;

        return $this;
    }

    /**
     * @return int
     */
    public function getDestinationId()
    {
        return $this->destinationId;
    }

    /**
     * @param int $destinationId
     *
     * @return self
     */
    public function setDestinationId($destinationId)
    {
        $this->destinationId = $destinationId;

        return $this;
    }
}
