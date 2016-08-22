<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Sulu\Bundle\Sales\CoreBundle\Manager;

use Sulu\Bundle\Sales\CoreBundle\Model\CustomerType;
use Symfony\Component\Translation\TranslatorInterface;

class CustomerTypeManager
{
    /**
     * @return CustomerType
     */
    public static function getTypeOrganization(TranslatorInterface $translator)
    {
        $customerType = new CustomerType();
        $customerType->setId(1);
        $customerType->setName(
            $translator->trans(
                'salescore.customer-type.organization',
                [],
                'backend'
            )
        );

        return $customerType;
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return CustomerType
     */
    public static function getTypePrivatePerson(TranslatorInterface $translator)
    {
        $customerType = new CustomerType();
        $customerType->setId(2);
        $customerType->setName(
            $translator->trans(
                'salescore.customer-type.private-person',
                [],
                'backend'
            )
        );

        return $customerType;
    }

    /**
     * @return CustomerType[]
     */
    public static function getAll(TranslatorInterface $translator)
    {
        $customerType = [];
        $customerType[] = CustomerTypeManager::getTypeOrganization($translator);
        $customerType[] = CustomerTypeManager::getTypePrivatePerson($translator);

        return $customerType;
    }
}
