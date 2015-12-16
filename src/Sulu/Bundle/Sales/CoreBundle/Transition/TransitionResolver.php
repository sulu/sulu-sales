<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
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
     * Returns the current, all previous and following transitions.
     *
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @throws EntityNotFoundException
     *
     * @return array
     */
    public function getTransitions($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        $current = $this->getCurrentTransition($alias, $id, $hydrationMode);

        if ($current === null) {
            throw new EntityNotFoundException($alias, $id);
        }
        $currentTransition = $current->getTransition();

        $allTransitions = [
            'previous' => array_reverse($this->getPreviousTransitions(
                $currentTransition->getSource(),
                $currentTransition->getSourceId(),
                $hydrationMode)
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
     * @param bool $fullObject
     *
     * @throws EntityNotFoundException
     *
     * @return array
     */
    public function resolveTransitionItems($transitionId, $fullObject = false)
    {
        $transition = $this->transitionManager->findById($transitionId);

        if ($transition === null) {
            throw new EntityNotFoundException(Transition::class, $transitionId);
        }
        $resolvedItems = [];

        /** @var TransitionItem $item */
        foreach ($transition->getItems() as $index => $item) {
            if ($fullObject) {
                $itemRepository = $this->entityManager->getRepository($item->getItemClass());
                $resolvedItem = $itemRepository->find($item->getItemId());

                if ($resolvedItem === null) {
                    continue;
                }

                $resolvedItems[$index]['item'] = $resolvedItem;
            }
            $resolvedItems[$index]['itemId'] = $item->getItemId();
            $resolvedItems[$index]['quantity'] = $item->getItemCount();
        }

        return $resolvedItems;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
     *
     * @throws EntityNotFoundException
     *
     * @return TransitionResult
     */
    protected function getCurrentTransition($alias, $id, $hydrationMode = self::HYDRATION_MODE_OBJECTS)
    {
        /** @var Transition $transition */
        $transition = $this->transitionManager->findOneByDestination($alias, $id);

        if ($transition === null) {
            throw new EntityNotFoundException($alias, $id);
        }

        $transitionResult = $this->createTransitionResult($alias, $id, $hydrationMode);

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

        if ($transition !== null) {
            $transitionResult = $this->createTransitionResult(
                $transition->getDestination(),
                $transition->getDestinationId(),
                $hydrationMode
            );

            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            $transitionResults[] = $transitionResult;

            $currentAlias = $transition->getSource();
            $currentId = $transition->getSourceId();

            // recursive call to get all previous of previous....
            while ($previousTransition = $this->getNextPreviousTransition($currentAlias, $currentId, $hydrationMode)) {
                $transitionResults[] = $previousTransition;
                $currentAlias = $previousTransition->getTransition()->getSource();
                $currentId = $previousTransition->getTransition()->getSourceId();
            }

            $firstTransitionResult = $this->createTransitionResult($currentAlias, $currentId, $hydrationMode);
            $transitionResults[] = $firstTransitionResult;
        } else {
            // no previous found - use the first source
            $transition = $this->transitionManager->findOneBySource($alias, $id);

            $firstTransitionResult = $this->createTransitionResult(
                $transition->getSource(),
                $transition->getSourceId(),
                $hydrationMode
            );
            $transitionResults[] = $firstTransitionResult;
        }

        return $transitionResults;
    }

    /**
     * Returns previous of previous Transition.
     *
     * @param string $destination
     * @param int $destinationId
     * @param string $hydrationMode
     *
     * @return null|TransitionResult
     */
    protected function getNextPreviousTransition($destination, $destinationId, $hydrationMode)
    {
        $transition = $this->transitionManager->findOneByDestination($destination, $destinationId);

        if ($transition !== null) {
            $transitionResult = $this->createTransitionResult(
                $transition->getDestination(),
                $transition->getDestinationId(),
                $hydrationMode
            );

            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            return $transitionResult;
        }

        return null;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param string $hydrationMode
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

            if (method_exists($entity, 'getNumber')) {
                $number = $entity->getNumber();
            }
            if (method_exists($entity, 'getCreated')) {
                $created = $entity->getCreated();
            }
        }
        $transitionResult->setNumber($number);
        $transitionResult->setCreated($created);

        return $transitionResult;
    }

    /**
     * Returns next following Transition.
     *
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

            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            $transitionResults[] = $transitionResult;

            $currentAlias = $transition->getDestination();
            $currentId = $transition->getDestinationId();

            // recursive call to get all previous of previous....
            while ($nextTransition = $this->getNextFollowingTransition($currentAlias, $currentId, $hydrationMode)) {
                $transitionResults[] = $nextTransition;
                $currentAlias = $nextTransition->getTransition()->getDestination();
                $currentId = $nextTransition->getTransition()->getDestinationId();
            }
        }

        return $transitionResults;
    }

    /**
     * @param string $source
     * @param int $sourceId
     * @param string $hydrationMode
     *
     * @return null|TransitionResult
     */
    protected function getNextFollowingTransition($source, $sourceId, $hydrationMode)
    {
        $transition = $this->transitionManager->findOneBySource($source, $sourceId);

        if ($transition !== null) {
            $transitionResult = $this->createTransitionResult(
                $transition->getDestination(),
                $transition->getDestinationId(),
                $hydrationMode
            );

            $transitionResult->setId($transition->getId());
            $transitionResult->setTransition($transition);

            return $transitionResult;
        }

        return null;
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
