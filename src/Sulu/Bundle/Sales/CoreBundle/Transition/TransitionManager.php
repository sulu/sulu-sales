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
     * @param string $destinationAlias
     * @param int $destinationId
     *
     * @return Transition[]
     */
    public function findByDestination($destinationAlias, $destinationId)
    {
        return $this->transitionRepository->findBy(
            [
                'destination' => $destinationAlias,
                'destinationId' => $destinationId
            ]
        );
    }

    /**
     * @param string $sourceAlias
     * @param int $sourceId
     *
     * @return Transition[]
     */
    public function findBySource($sourceAlias, $sourceId)
    {
        return $this->transitionRepository->findBy(
            [
                'source' => $sourceAlias,
                'sourceId' => $sourceId
            ]
        );
    }

    /**
     * @param string $destinationAlias
     * @param int $destinationId
     *
     * @return Transition
     */
    public function findOneByDestination($destinationAlias, $destinationId)
    {
        return $this->transitionRepository->findOneBy(
            [
                'destination' => $destinationAlias,
                'destinationId' => $destinationId
            ]
        );
    }

    /**
     * @param string $sourceAlias
     * @param int $sourceId
     *
     * @return Transition
     */
    public function findOneBySource($sourceAlias, $sourceId)
    {
        return $this->transitionRepository->findOneBy(
            [
                'source' => $sourceAlias,
                'sourceId' => $sourceId
            ]
        );
    }

}
