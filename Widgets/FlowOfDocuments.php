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

use DateTime;
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
     * Creates and adds an entry to the exisiting entries
     * @param String|Number $id
     * @param String $number
     * @param String $type
     * @param DateTime $date
     * @param String $route
     */
    protected function addEntry($id, $number, $type, DateTime $date, $route)
    {
        $this->entries[] = array(
            'id' => $id,
            'number' => $number,
            'type' => $type,
            'date' => $date,
            'route' => $route
        );
    }

    /**
     * Sorts the data array by the date
     *
     * @param bool $desc
     */
    protected function orderDataByDate($desc = true)
    {
        usort(
            $this->entries,
            function ($a, $b) use ($desc) {
                if ($a['date'] > $b['date']) {
                    if (!$desc) {
                        return 1;
                    }
                    return -1;
                } elseif ($a['date'] < $b['date']) {
                    if (!$desc) {
                        return -1;
                    }
                    return 1;
                }
                return 0;
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

    /**
     * Serializes given entries and returns them
     *
     * @param $dateFormat
     * @return mixed
     */
    protected function serializeData($dateFormat = DateTime::W3C)
    {
        return $this->parseDates($this->entries, $dateFormat);
    }

    /**
     * Parses dates according to a given format
     *
     * @param $data
     * @param string $format
     * @return mixed
     */
    private function parseDates($data, $format)
    {
        foreach ($data as $key => $entry) {
            $data[$key]['date'] = $data[$key]['date']->format($format);
        }

        return $data;
    }
}
