<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Sulu\Bundle\Sales\CoreBundle\Entity;

interface ItemAttributeInterface
{
    /**
     * Set attribute
     *
     * @param string $attribute
     *
     * @return ItemAttribute
     */
    public function setAttribute($attribute);

    /**
     * Get attribute
     *
     * @return string
     */
    public function getAttribute();

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ItemAttribute
     */
    public function setValue($value);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set item
     *
     * @param ItemInterface $item
     *
     * @return ItemAttribute
     */
    public function setItem(ItemInterface $item = null);

    /**
     * Get item
     *
     * @return ItemInterface
     */
    public function getItem();
}
