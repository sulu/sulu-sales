<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Sulu\Bundle\TestBundle\Behat\DefaultContext;

/**
 * Behat context class for the InquiryBundle.
 */
class ShippingContext extends DefaultContext
{
    /**
     * @inheritdoc
     */
    public function initEnv(BeforeScenarioScope $scope)
    {
        // Do nothing in this context.
    }
}
