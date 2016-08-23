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
    const TRANS_DOMAIN = 'backend';

    const TYPE_ORGANIZATION_ID = 1;
    const TYPE_PRIVATE_PERSON_ID = 2;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param string $locale
     *
     * @return CustomerType
     */
    public function retrieveTypeOrganization($locale)
    {
        $customerType = new CustomerType();
        $customerType->setId(self::TYPE_ORGANIZATION_ID);
        $customerType->setName(
            $this->retrieveTranslator($locale)->trans(
                'salescore.customer-type.organization',
                [],
                self::TRANS_DOMAIN
            )
        );

        return $customerType;
    }

    /**
     * @param string $locale
     *
     * @return CustomerType
     */
    public function retrieveTypePrivatePerson($locale)
    {
        $customerType = new CustomerType();
        $customerType->setId(self::TYPE_PRIVATE_PERSON_ID);
        $customerType->setName(
            $this->retrieveTranslator($locale)->trans(
                'salescore.customer-type.private-person',
                [],
                self::TRANS_DOMAIN
            )
        );

        return $customerType;
    }

    /**
     * @param string $locale
     *
     * @return CustomerType[]
     */
    public function retrieveAll($locale)
    {
        return [
            self::retrieveTypeOrganization($locale),
            self::retrieveTypePrivatePerson($locale)
        ];
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    public function retrieveAllAsArray($locale)
    {
        $result = [];

        /** @var CustomerType $customerType */
        foreach (self::retrieveAll($locale) as $customerType) {
            $result[] = [
                'id' => $customerType->getId(),
                'name' => $customerType->getName(),
            ];
        }

        return $result;
    }

    /**
     * @param string $locale
     *
     * @return CustomerType
     */
    public function retrieveDefault($locale)
    {
        return self::retrieveTypeOrganization($locale);
    }

    /**
     * @param string $locale
     *
     * @return TranslatorInterface
     */
    private function retrieveTranslator($locale)
    {
        $this->translator->setLocale($locale);
        return $this->translator;
    }
}
