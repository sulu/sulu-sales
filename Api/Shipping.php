<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Component\Rest\ApiWrapper;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use Sulu\Component\Security\UserInterface;

/**
 * The Shipping class which will be exported to the API
 * @package Sulu\Bundle\Sales\ShippingBundle\Api
 * @Relation("self", href="expr('/api/admin/shippings/' ~ object.getId())")
 */
class Shipping extends ApiWrapper
{

}
