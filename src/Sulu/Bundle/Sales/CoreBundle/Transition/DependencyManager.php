<?php

namespace Sulu\Bundle\Sales\CoreBundle\Transition;

class DependencyManager
{
    /**
     * @var array
     */
    protected $classMap = [];

    /**
     * @param $entity
     * @param array $attributes
     */
    public function addMapping($entity, $attributes)
    {
        $this->classMap[$entity] = $attributes;
    }

    /**
     * @param string $alias
     *
     * @return mixed
     */
    public function getParametersForAlias($alias)
    {
        foreach ($this->classMap as $item => $parameters) {
            if ($parameters['alias'] == $alias) {
                $parameters['class'] = $item;

                return $parameters;
            }
        }

        return null;
    }
}
