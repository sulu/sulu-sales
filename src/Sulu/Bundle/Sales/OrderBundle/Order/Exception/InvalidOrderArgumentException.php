<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Order\Exception;

class InvalidOrderArgumentException extends OrderException
{
    /**
     * The name of the object not found
     * @var string
     */
    private $argument;

    public function __construct($argument)
    {
        $this->argument = '';
        parent::__construct('The argument "' . $this->argument. '" is not correct for order.', 0);
    }

    /**
     * Returns the argument that's invalid
     * @return string
     */
    public function getArgument()
    {
        return $this->argument;
    }
}
