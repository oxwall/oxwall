<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class BASE_CMP_SiteStatistic  extends OW_Component
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected $chartId;

    /**
     * Entities
     *
     * @var array
     */
    protected $entities;

    /**
     * Entity labels
     *
     * @var array
     */
    protected $entityLabels;

    /**
     * Period
     *
     * @var array
     */
    protected $period;

    /**
     * Class constructor
     *
     * @param string $chartId
     * @param array $entityTypes
     * @param string $period
     * @param array $entityLabels
     */
    public function __construct( $chartId, array $entityTypes, array $entityLabels, $period = BOL_SiteStatisticService::PERIOD_TYPE_TODAY )
    {
        parent::__construct();

        $this->chartId  = $chartId;
        $this->entityTypes = $entityTypes;
        $this->entityLabels = $entityLabels;
        $this->period = $period;
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        $service = BOL_SiteStatisticService::getInstance();

        // get statistics
        $entities = $service->getStatistics($this->entityTypes, $this->period);

        // translate and process the data entities
        $data  = array();
        $total = array();
        $index = 0;

        foreach ($entities as $entity => $values)
        {
            $list = array_values($values);

            $data[] = array_merge(array(
                'label' => $this->entityLabels[$entity],
                'data' => $list
            ), $this->getChartColor($index));

            $total[] = array(
                'label' => $this->entityLabels[$entity],
                'count' => array_sum($list)
            );

            $index++;
        }

        // assign view variables
        $this->assign('chartId', $this->chartId);
        $this->assign('categories', json_encode($this->entityCategories()));
        $this->assign('data', json_encode($data, JSON_NUMERIC_CHECK));
        $this->assign('total', $total);

        // include js and css files
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'chart.js');
    }

    /**
     * Get entity categories
     *
     * @return array
     */
    protected function entityCategories()
    {
        switch ($this->period)
        {
            case BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR :
                $categories =  $this->getMonths(12);
                break;

            case BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS :
            case BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS  :
                $categories =  $this->
                        getDays(($this->period == BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS ? 30 : 7));

                break;

            case BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY :
            case BOL_SiteStatisticService::PERIOD_TYPE_TODAY :
            default :
                $categories =  $this->getHours();
        }

        return $categories;
    }

    /**
     * Get months
     *
     * @param integer $count
     * @return array
     */
    protected function getMonths($count)
    {
        $months = array();
        $language = OW::getLanguage();

        for ($i = $count - 1; $i > 0; $i--)
        {
            $months[] = $language->
                    text('base', 'month_' . date('n', strtotime('today -' . $i . ' month')));
        }

        $months[] = $language->
                text('base', 'month_' . date('n', strtotime('today -' . $i . ' month')));

        return $months;
    }

    /**
     * Get hours
     *
     * @return array
     */
    protected function getHours()
    {
        $hours = array();
        $hours[] = '12:00 AM';
        $hour  = 1;

        for ($i = 0; $i < 23; $i++)
        {
            $suffix = $i < 11 ? 'AM' : 'PM';

            if ($i == 12)
            {
                $hour = 1;
            }

            $hours[] = $hour . ':00 ' . $suffix;
            $hour++;
        }

        return $hours;
    }

    /**
     * Get days
     *
     * @param integer $count
     * @return array
     */
    protected function getDays($count)
    {
        $days = array();

        for ($i = $count - 1; $i > 0; $i--)
        {
            $days[] = UTIL_DateTime::formatDate(strtotime('today -' . $i . ' days'), true);
        }

        $days[] = UTIL_DateTime::formatDate(strtotime('today'), true);

        return $days;
    }

    /**
     * Get chart color
     *
     * @param integer $num
     * @return array
     */
    protected function getChartColor($num)
    {
        $hash = md5('chart' . $num);

        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        return array(
            'fillColor' => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.2)',
            'strokeColor' => 'rgba(' . $r . ',' . $g . ',' .$b . ',1)',
            'pointColor' => 'rgba(' . $r . ',' .$g .',' . $b . ',1)',
            'pointStrokeColor' => '#fff',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(' .$r . ',' . $g .','. $b . ',1)'
        );
    }
}