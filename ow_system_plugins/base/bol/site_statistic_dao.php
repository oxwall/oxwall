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
 * Data Access Object for `base_site_statistic` table.
 * 
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SiteStatisticDao extends OW_BaseDao
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'entityType';

    /**
     * Entity id
     */
    const ENTITY_ID = 'entityId';

    /**
     * Entity count
     */
    const ENTITY_COUNT = 'entityCount';

    /**
     * Timestamp
     */
    const TIMESTAMP = 'timeStamp';

    /**
     * Report hour
     */
    const REPORT_TYPE_HOUR = 'hour';

    /**
     * Report day
     */
    const REPORT_TYPE_DAY = 'day';

    /**
     * Report month
     */
    const REPORT_TYPE_MONTH = 'month';

    /**
     * Singleton instance.
     *
     * @var BOL_SiteStatisticDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SiteStatisticDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_SiteStatistic';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_site_statistic';
    }

    /**
     * Get last year statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLastYearStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -11 months');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getMonths(12), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_MONTH);
    }

    /**
     * Get last 30 days statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLast30DaysStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -30 days');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getDays(30), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_DAY);
    }

    /**
     * Get last 7 days statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLast7DaysStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -6 days');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getDays(7), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_DAY);
    }

    /**
     * Get today statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getTodayStatistics(array $entityTypes)
    {
        $timeStart  = strtotime('today');
        $timeEnd    = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getHours(), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_HOUR);
    }

    /**
     * Get yesterday statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getYesterdayStatistics(array $entityTypes)
    {
        $timeStart = strtotime('yesterday');
        $timeEnd   = strtotime('yesterday 23:59:59');

        return $this->getStatistics($this->
                getHours(), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_HOUR);
    }

    /**
     * Get months
     *
     * @apram integer $count
     * @return array
     */
    protected function getMonths($count)
    {
        $categories = array();

        for ($i = $count - 1; $i > 0; $i--)
        {
            $categories[date('n' , strtotime('today -' . $i . ' months'))] = 0;
        }

        $categories[date('n' , strtotime('today'))] = 0;

        return $categories;
    }

    /**
     * Get days
     *
     * @apram integer $count
     * @return array
     */
    protected function getDays($count)
    {
        $categories = array();

        for ($i = $count - 1; $i > 0; $i--)
        {
            $categories[date('j' , strtotime('today -' . $i . ' days'))] = 0;
        }

        $categories[date('j' , strtotime('today'))] = 0;

        return $categories;
    }

    /**
     * Get hours
     *
     * @return array
     */
    protected function getHours()
    {
        return array_pad(array(), 24, 0);
    }

    /**
     * Get statistics
     *
     * @apram array $categories
     * @param array $entityTypes
     * @param integer $timeStart
     * @param integer $timeEnd
     * @param string $reportType
     * @return array
     */
    protected function getStatistics(array $categories, array $entityTypes, $timeStart, $timeEnd, $reportType)
    {
        $report = array();
        foreach ($entityTypes as $type)
        {
            $report[$type] = $categories;
        }

        $query = '
            SELECT
                `' . self::ENTITY_TYPE . '`,
                DATE_FORMAT(FROM_UNIXTIME(`' . self::TIMESTAMP . '`), "' . $this->getReportDateFormat($reportType) . '") AS `category`,
                SUM(`' . self::ENTITY_COUNT . '`) as `count`
            FROM
                `' . $this->getTableName() . '`
            WHERE
                `' . self::ENTITY_TYPE . '` IN (' . $this->dbo->mergeInClause($entityTypes) . ')
                    AND
                `' . self::TIMESTAMP . '` >= ' . (int) $timeStart . '
                    AND
                `' . self::TIMESTAMP . '` <= ' . (int) $timeEnd   . '
            GROUP BY
                `' . self::ENTITY_TYPE . '`,
                `category`';

        $values =  $this->dbo->queryForList($query);

        if ( $values )
        {
            // fill report array with values
            foreach ($values as $value)
            {
                $report[$value['entityType']][$value['category']] = $value['count'];
            }
        }

        return $report;
    }

    /**
     * Get report date format
     *
     * @param string $reportType
     * @return string
     */
    protected function getReportDateFormat($reportType)
    {
        switch ( $reportType )
        {
            case self::REPORT_TYPE_MONTH :
                return '%c';
                break;

            case self::REPORT_TYPE_DAY :
                return '%e';
                break;

            case self::REPORT_TYPE_HOUR :
            default :
                return '%k';
        }
    }

    /**
     * Get statistics
     * 
     * @param array $entityTypes
     * @param string $startDate
     * @param string $endDate
     * @param string $reportType
     * @return array
     */
    /*public function getStatistics(array $entityTypes, $startDate, $endDate, $reportType)
    {
        $startDate = strtotime($startDate);
        $endDate   = strtotime($endDate . ' 23:59:59');

        // get an report array
        $report = $this->getReportArray($entityTypes, $reportType);

        // create an empty values array
        $query = '
            SELECT 
                `' . self::ENTITY_TYPE . '`,
                DATE_FORMAT(FROM_UNIXTIME(`' . self::TIMESTAMP . '`), "' . $this->getReportDateFormat($reportType) . '") AS `category`,
                SUM(`' . self::ENTITY_COUNT . '`) as `count` 
            FROM 
                `' . $this->getTableName() . '`
            WHERE
                `' . self::ENTITY_TYPE . '` IN (' . $this->dbo->mergeInClause($entityTypes) . ')
                    AND
                `' . self::TIMESTAMP . '` >= ' . $startDate . '
                    AND
                `' . self::TIMESTAMP . '` <= ' . $endDate   . '
            GROUP BY 
                `' . self::ENTITY_TYPE . '`, 
                `category`';

        $values = $this->dbo->queryForList($query);

        if ( $values )
        {
            // fill report array with values
            foreach ($values as $value)
            {
                $report[$value['entityType']][$value['category']] = $value['count'];
            }
        }

        return $report;
    }
*/
    /**
     * Get report array
     * 
     * @param array $entityTypes
     * @param string $reportType
     * @return array
     */
   /* protected function getReportArray(array $entityTypes, $reportType)
    {
        switch ( $reportType ) 
        {
            case self::REPORT_TYPE_MONTH :
                $values = $this->getMonthsArray();
                break;

            case self::REPORT_TYPE_DAY :
                $values = $this->getDaysArray();
                break;

            case self::REPORT_TYPE_WEEK :
                $values = $this->getWeeksArray();
                break;

            case self::REPORT_TYPE_HOUR :
            default :
                $values = $this->getHoursArray();
        }

        // fill entities array with values
        foreach ($entityTypes as $entityType)
        {
            $reportArray[$entityType] = $values;
        }

        return $reportArray;
    }
*/
    /**
     * Get months array
     * 
     * @return array
     */
   /* protected function getMonthsArray()
    {
        return array(
            'January'   => 0,
            'February'  => 0,
            'March'     => 0,
            'April'     => 0,
            'May'       => 0,
            'June'      => 0,
            'July'      => 0,
            'August'    => 0,
            'September' => 0,
            'October'   => 0,
            'November'  => 0,
            'December'  => 0
        );
    }
*/
    /**
     * Get days array
     * 
     * @return array
     */
  /*  public function getDaysArray()
    {
        $days = array();

        for ($i = 1; $i <= 31; $i++) 
        {
            $index = $i <= 9 ? '0' . $i : $i;
            $days[$index] = 0;
        }

        return $days;
    }
*/
    /**
     * Get weeks array
     * 
     * @return array
     */
   /* protected function getWeeksArray()
    {
        return array(
            'Monday'    => 0,
            'Tuesday'   => 0,
            'Wednesday' => 0,
            'Thursday'  => 0,
            'Friday'    => 0,
            'Saturday'  => 0,
            'Sunday'    => 0
        );
    }*/

    /**
     * Get hours array
     * 
     * @return array
     */
    /*public function getHoursArray()
    {
        $hours = array();

        for ($i = 0; $i <= 23; $i++) 
        {
            $index = $i <= 9 ? '0' . $i : $i;
            $hours[$index] = 0;
        }

        return $hours;
    }*/

    /**
     * Get report date format
     * 
     * @param string $reportType
     */
    /*protected function getReportDateFormat($reportType)
    {
        switch($reportType) {
            case self::REPORT_TYPE_MONTH :
                return '%M';

            case self::REPORT_TYPE_DAY :
                return '%d';

            case self::REPORT_TYPE_WEEK :
                return '%W';

            case self::REPORT_TYPE_HOUR :
            default :
                return '%H';
        }
    }*/
}