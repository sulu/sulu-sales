<?php
/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Sulu\Bundle\Sales\CoreBundle\Transition\TransitionData\TransitionData;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for transfer one entity into another.
 */
class TransitionEvent extends Event
{
    /**
     * @var string
     */
    protected $referenceKey;

    /**
     * @var string
     */
    protected $targetKey;

    /**
     * @var TransitionData
     */
    protected $data;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param string $targetKey Target entity key.
     * @param string $referenceKey Source entity key.
     * @param TransitionData $data
     * @param string $locale
     */
    public function __construct($targetKey, $referenceKey, TransitionData $data, $locale)
    {
        $this->targetKey = $targetKey;
        $this->referenceKey = $referenceKey;
        $this->data = $data;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getReferenceKey()
    {
        return $this->referenceKey;
    }

    /**
     * @return string
     */
    public function getTargetKey()
    {
        return $this->targetKey;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
