<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Widgets;

use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;

/**
 * Class FlowOfDocuments
 * Abstract class for widgets which show flow of documents (invoice, order, etc)
 *
 * @package Sulu\Bundle\Sales\CoreBundle\Widgets
 */
abstract class FlowOfDocuments implements WidgetInterface
{
    private $entries = [];

    /**
     * @return array []
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param array $data
     */
    public function addEntry(array $data)
    {
        $this->entries[] = $data;
    }

    /**
     * Sorts the data array by the date
     * @param bool $desc
     */
    protected function orderDataByDate($desc = true)
    {
        usort(
            $this->entries,
            function ($a, $b) use ($desc) {
                if ($a['date'] > $b['date']) {
                    if(!$desc) {
                        return 1;
                    }
                    return -1;
                } elseif ($a['date'] < $b['date']) {
                    if(!$desc){
                        return -1;
                    }
                    return 1;
                }
            }
        );
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesCoreBundle:Widgets:core.flow.of.documents.html.twig';
    }
}
