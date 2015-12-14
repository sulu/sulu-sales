<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

use Sulu\Bundle\Sales\CoreBundle\Entity\Transition;
use Sulu\Bundle\Sales\CoreBundle\Entity\TransitionRepository;

class TransitionManager
{
    /**
     * @var TransitionRepository
     */
    protected $transitionRepository;

    /**
     * @param TransitionRepository $transitionRepository
     */
    public function __construct(TransitionRepository $transitionRepository)
    {
        $this->transitionRepository = $transitionRepository;
    }

    /**
     * @param string $destinationClass
     * @param int $destinationId
     *
     * @return Transition[]
     */
    public function findByDestination($destinationClass, $destinationId)
    {
        return $this->transitionRepository->findBy(
            [
                'destination' => $destinationClass,
                'destinationId' => $destinationId
            ]
        );
    }

    /**
     * @param string $sourceClass
     * @param int $sourceId
     *
     * @return Transition[]
     */
    public function findBySource($sourceClass, $sourceId)
    {
        return $this->transitionRepository->findBy(
            [
                'source' => $sourceClass,
                'sourceId' => $sourceId
            ]
        );
    }

    /**
     * @param string $destinationClass
     * @param int $destinationId
     *
     * @return Transition
     */
    public function findOneByDestination($destinationClass, $destinationId)
    {
        return $this->transitionRepository->findOneBy(
            [
                'destination' => $destinationClass,
                'destinationId' => $destinationId
            ]
        );
    }

    /**
     * @param string $sourceClass
     * @param int $sourceId
     *
     * @return Transition
     */
    public function findOneBySource($sourceClass, $sourceId)
    {
        return $this->transitionRepository->findOneBy(
            [
                'source' => $sourceClass,
                'sourceId' => $sourceId
            ]
        );
    }

}
