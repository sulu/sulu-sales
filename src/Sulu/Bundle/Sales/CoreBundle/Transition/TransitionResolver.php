<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sulu\Bundle\Sales\CoreBundle\Entity\Transition;
use Sulu\Bundle\Sales\CoreBundle\Entity\TransitionItem;

class TransitionResolver
{
    const HYDRATION_MODE_OBJECTS = 'HYDRATION_MODE_OBJECTS';

    /**
     * @var TransitionManager
     */
    protected $transitionManager;

    /**
     * @var DependencyManager
     */
    protected $dependencyManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param TransitionManager $transitionManager
     * @param DependencyManager $dependencyManager
     * @param EntityManager $entityManager
     */
    public function __construct(
        TransitionManager $transitionManager,
        DependencyManager $dependencyManager,
        EntityManager $entityManager
    ) {
        $this->transitionManager = $transitionManager;
        $this->dependencyManager = $dependencyManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @return array[]
     */
    public function getTransitions($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        $current = $this->getCurrentTransition($alias, $id, $hydrationMode);
        $currentTransition = $current->getTransition();

        $allTransitions = [
            'previous' => $this->getPreviousTransitions(
                $currentTransition->getSource(),
                $currentTransition->getSourceId(),
                $hydrationMode
            ),
            'current' => $current,
            'following' => $this->getFollowingTransitions(
                $currentTransition->getDestination(),
                $currentTransition->getDestinationId(),
                $hydrationMode
            )
        ];

        return $allTransitions;
    }

    /**
     * @param int $transitionId
     *
     * @return TransitionItem[]
     */
    public function getTransitionItems($transitionId)
    {
        // TODO
        return null;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @return TransitionResult
     */
    protected function getCurrentTransition($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        /** @var Transition $transition */
        $transition = $this->transitionManager->findOneByDestination($alias, $id);

        $transitionResult = $this->createTransitionResult($alias, $id, $hydrationMode);

        $transitionResult->setItems($transition->getItems()->toArray());
        $transitionResult->setId($transition->getId());
        $transitionResult->setTransition($transition);

        return $transitionResult;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @return TransitionResultInterface[]
     */
    protected function getPreviousTransitions($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        $transitionResults = [];
        $transition = $this->transitionManager->findOneByDestination($alias, $id);

        if ($transition != null) {
            $transitionResult = $this->createTransitionResult(
                $transition->getDestination(),
                $transition->getDestinationId(),
                $hydrationMode
            );

            $transitionResult->setItems($transition->getItems()->toArray());
            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            $transitionResults[] = $transitionResult;

            $currentAlias = $transition->getSource();
            $currentId = $transition->getSourceId();

            // recursive call to get all previous of previous....
            while ($previousTransitions = $this->getPreviousTransitions($currentAlias, $currentId, $hydrationMode)) {
                array_merge($transitionResults, $previousTransitions);
                $currentAlias = $previousTransitions[0]->getTransition()->getSource();
                $currentId = $previousTransitions[0]->getTransition()->getSourceId();
            }

            $firstTransitionResult = $this->createTransitionResult($currentAlias, $currentId, $hydrationMode);

            $transitionResults[] = $firstTransitionResult;

        }
        $transitionResults = array_reverse($transitionResults);

        return $transitionResults;
    }

    /**
     * @param $alias
     * @param $id
     * @param $hydrationMode
     *
     * @return TransitionResult
     */
    protected function createTransitionResult($alias, $id, $hydrationMode)
    {
        $parameters = $this->dependencyManager->getParametersForAlias($alias);
        $transitionResult = new TransitionResult();

        $this->setParameters($transitionResult, $parameters, $id);

        $number = null;
        $created = null;
        if ($hydrationMode == self::HYDRATION_MODE_OBJECTS) {
            /** @var EntityRepository $entityRepository */
            $entityRepository = $this->entityManager->getRepository($parameters['class']);
            $entity = $entityRepository->find($id);

            $number = $entity->getNumber();
            $created = $entity->getCreated();
        }
        $transitionResult->setNumber($number);
        $transitionResult->setCreated($created);

        return $transitionResult;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @return TransitionResult[]
     */
    protected function getFollowingTransitions($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        $transitionResults = [];
        $transition = $this->transitionManager->findOneBySource($alias, $id);

        if ($transition) {
            $transitionResult = $this->createTransitionResult(
                $transition->getDestination(),
                $transition->getDestinationId(),
                $hydrationMode
            );

            $transitionResult->setItems($transition->getItems()->toArray());
            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            $transitionResults[] = $transitionResult;

            $currentAlias = $transition->getDestination();
            $currentId = $transition->getDestinationId();

            // recursive call to get all previous of previous....
            while ($previousTransitions = $this->getFollowingTransitions($currentAlias, $currentId, $hydrationMode)) {
                array_merge($transitionResults, $previousTransitions);
                $currentAlias = $previousTransitions[0]->getTransition()->getDestination();
                $currentId = $previousTransitions[0]->getTransition()->getDestinationId();
            }
        }

        return $transitionResults;
    }

    /**
     * @param TransitionResultInterface $transitionResult
     * @param array $parameters
     * @param int $id
     */
    protected function setParameters(
        TransitionResultInterface $transitionResult,
        array $parameters,
        $id
    ) {
        if (isset($parameters['icon'])) {
            $transitionResult->setIcon($parameters['icon']);
        }
        if (isset($parameters['link'])) {
            $link = str_replace(':id', $id, $parameters['link']);
            $transitionResult->setLink($link);
        }
        if (isset($parameters['pdf'])) {
            $pdf = str_replace(':id', $id, $parameters['pdf']);
            $transitionResult->setPdfUrl($pdf);
        }
    }
}
