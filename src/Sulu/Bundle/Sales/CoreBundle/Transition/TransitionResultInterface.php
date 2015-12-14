<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Sulu\Bundle\Sales\CoreBundle\Entity\Transition;

interface TransitionResultInterface
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param string $icon
     *
     * @return self
     */
    public function setIcon($icon);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     *
     * @return self
     */
    public function setLink($link);

    /**
     * @return \DateTime
     */
    public function getCreated();

    /**
     * @return string
     */
    public function getPdfUrl();

    /**
     * @param string $pdfUrl
     *
     * @return self
     */
    public function setPdfUrl($pdfUrl);

    /**
     * @var array
     */
    public function getItems();

    /**
     * @return Transition
     */
    public function getTransition();
}
