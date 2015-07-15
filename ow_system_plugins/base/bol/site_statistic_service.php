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
 * Site statistics service.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.6
 */
class BOL_SiteStatisticService
{
    /**
     * Site statistics dao
     * @var BOL_SiteStatisticDao
     */
    private $siteStatisticsDao;

    /**
     * Singleton instance.
     *
     * @var BOL_SiteStatisticService
     */
    private static $classInstance;

    /**
     * Period type last year
     */
    const PERIOD_TYPE_LAST_YEAR = 'last_year';

    /**
     * Period type last 30 days
     */
    const PERIOD_TYPE_LAST_30_DAYS = 'last_30_days';

    /**
     * Period type last 7 days
     */
    const PERIOD_TYPE_LAST_7_DAYS = 'last_7_days';

    /**
     * Period type yesterday
     */
    const PERIOD_TYPE_YESTERDAY = 'yesterday';

    /**
     * Period type today
     */
    const PERIOD_TYPE_TODAY = 'today';

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->siteStatisticsDao = BOL_SiteStatisticDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SiteStatisticService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Add entity
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param integer $entityCount
     * @return void
     */
    public function addEntity($entityType, $entityId, $entityCount = 1)
    {
        $siteStatisticsDto = new BOL_SiteStatistic();
        $siteStatisticsDto->entityId = $entityId;
        $siteStatisticsDto->entityType = $entityType;
        $siteStatisticsDto->entityCount = $entityCount;
        $siteStatisticsDto->timeStamp = time();

        $this->siteStatisticsDao->saveDelayed($siteStatisticsDto);
    }

    /**
     * Get statistics
     *
     * @param array $entities
     * @param string $period
     * @return array
     */
    public function getStatistics(array $entities, $period = self::PERIOD_TYPE_TODAY)
    {
        switch ($period)
        {
            case self::PERIOD_TYPE_LAST_YEAR :
                $statistics =  $this->siteStatisticsDao->getLastYearStatistics($entities);
                break;

            case self::PERIOD_TYPE_LAST_30_DAYS :
                $statistics =  $this->siteStatisticsDao->getLast30DaysStatistics($entities);
                break;

            case self::PERIOD_TYPE_LAST_7_DAYS :
                $statistics =  $this->siteStatisticsDao->getLast7DaysStatistics($entities);
                break;

            case self::PERIOD_TYPE_YESTERDAY :
                $statistics =  $this->siteStatisticsDao->getYesterdayStatistics($entities);
                break;

            case self::PERIOD_TYPE_TODAY :
            default :
                $statistics =  $this->siteStatisticsDao->getTodayStatistics($entities);
        }

        return $statistics;
    }
}