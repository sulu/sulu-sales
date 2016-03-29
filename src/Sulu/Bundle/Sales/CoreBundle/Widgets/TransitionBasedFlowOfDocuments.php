<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Widgets;

use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\Sales\CoreBundle\Transition\TransitionResolver;
use Sulu\Bundle\Sales\CoreBundle\Transition\TransitionResultInterface;
use Sulu\Bundle\Sales\CoreBundle\Widgets\FlowOfDocuments as FlowOfDocumentsBase;

abstract class TransitionBasedFlowOfDocuments extends FlowOfDocumentsBase
{
    /**
     * @var string Name of this widget
     */
    protected $widgetName = '';

    /**
     * @var TransitionResolver
     */
    protected $transitionResolver;

    /**
     * @var string Defines the name of this transition.
     */
    protected $transitionKey;

    /**
     * Key in options array, that specifies the id of the current entity.
     *
     * @var string
     */
    protected $optionsIdKey;


    /**
     * @param TransitionResolver $transitionResolver
     */
    public function __construct(
        TransitionResolver $transitionResolver
    ) {
        $this->transitionResolver = $transitionResolver;
    }

    /**
     * Returns data to render template.
     *
     * @param array $options
     *
     * @throws WidgetException
     *
     * @return array
     */
    public function getData($options)
    {
        $this->checkRequiredParameters($options, [$this->optionsIdKey]);

        $transitions = $this->transitionResolver->getTransitions(
            $this->transitionKey,
            $options[$this->optionsIdKey]
        );

        /** @var TransitionResultInterface $transition */
        if (isset($transitions['previous'])) {
            foreach ($transitions['previous'] as $transition) {
                $this->addTransitionToEntries($transition);
            }
        }

        /** @var TransitionResultInterface $transition */
        $transition = $transitions['current'];
        $this->addTransitionToEntries($transition);

        if (isset($transitions['following'])) {
            /** @var TransitionResultInterface $transition */
            foreach ($transitions['following'] as $transition) {
                $this->addTransitionToEntries($transition);
            }
        }

        return parent::serializeData();
    }

    /**
     * @param TransitionResultInterface $transition
     */
    protected function addTransitionToEntries(TransitionResultInterface $transition)
    {
        parent::addEntry(
            $transition->getDestinationId(),
            $transition->getNumber(),
            $transition->getIcon(),
            $transition->getCreated(),
            $transition->getLink(),
            $transition->getPdfUrl(),
            $transition->getTranslationKey()
        );
    }
}
