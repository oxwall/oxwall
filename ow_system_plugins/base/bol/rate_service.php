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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_RateService
{
    const CONFIG_MAX_RATE = 'max_rate';

    /**
     * @var BOL_RateDao
     */
    private $rateDao;
    /**
     * @var array
     */
    private $configs = array();
    /**
     * Singleton instance.
     *
     * @var BOL_RateService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RateService
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
    private function __construct()
    {
        $this->rateDao = BOL_RateDao::getInstance();
        $this->configs[self::CONFIG_MAX_RATE] = 5;
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Returns config value.
     *
     * @param string $name
     * @return mixed
     */
    public function getConfig( $name )
    {
        return $this->configs[trim($name)];
    }

    /**
     * Saves and updates rate items.
     *
     * @param BOL_Rate $rateItem
     */
    public function saveRate( BOL_Rate $rateItem )
    {
        $this->rateDao->save($rateItem);
    }

    /**
     * Deletes rate item by id.
     *
     * @param integer $rateId
     */
    public function deleteRate( $rateId )
    {
        $this->rateDao->deleteById($rateId);
    }

    /**
     * Returns rate item for provided entity id, entity type and user id.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Rate
     */
    public function findRate( $entityId, $entityType, $userId )
    {
        return $this->rateDao->findRate($entityId, $entityType, $userId);
    }

    /**
     * Returns rate info for provided entity id and entity type.
     * Example: array( 'avg_rate' => 5, 'rates_count' => 35 ).
     *
     * @param integer $entityId
     * @param integer $entityType
     * @return array
     */
    public function findRateInfoForEntityItem( $entityId, $entityType )
    {
        return $this->rateDao->findEntityItemRateInfo($entityId, $entityType);
    }

    /**
     * Returns rate info for provided entity id and entity type.
     * Example: array( 'entity_id' => array( 'avg_score' => 5, 'rates_count' => 35 ) ).
     *
     * @param array<integer> $entityIdList
     * @param integer $entityType
     * @return array
     */
    public function findRateInfoForEntityList( $entityType, $entityIdList )
    {
        $result = $this->rateDao->findRateInfoForEntityList($entityType, $entityIdList);

        $resultArray = array();

        foreach ( $result as $item )
        {
            $resultArray[$item['entityId']] = $item;
        }

        foreach ( $entityIdList as $id )
        {
            if ( !isset($resultArray[$id]) )
            {
                $resultArray[$id] = array('rates_count' => 0, 'avg_score' => 0);
            }
        }

        return $resultArray;
    }

    public function findMostRatedEntityList( $entityType, $first, $count, $exclude = null )
    {
        $arr = $this->rateDao->findMostRatedEntityList($entityType, $first, $count, $exclude);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }

    public function findMostRatedEntityCount( $entityType, $exclude = null )
    {
        return $this->rateDao->findMostRatedEntityCount($entityType, $exclude);
    }

    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $status = $status ? 1 : 0;

        $this->rateDao->updateEntityStatus($entityType, $entityId, $status);
    }

    /**
     * Removes all user rates.
     *
     * @param integer $userId
     */
    public function deleteUserRates( $userId )
    {
        $this->rateDao->deleteUserRates($userId);
    }

    /**
     * Removes all entity item rates.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityRates( $entityId, $entityType )
    {
        $this->rateDao->deleteEntityItemRates($entityId, $entityType);
    }

    public function deleteEntityTypeRates( $entityType )
    {
        $this->rateDao->deleteByEntityType($entityType);
    }

    public function updateEntityStatus( $entityType, $entityId, $status = true )
    {
        $this->rateDao->updateEntityStatus($entityType, (int)$entityId, (int)$status);
    }

    public function findUserSocre( $userId, $entityType, array $entityIdList )
    {
        $score = $this->rateDao->findUserScore($userId, $entityType, $entityIdList);
        $result = array();

        foreach ( $score as $val )
        {
            $result[$val[BOL_RateDao::ENTITY_ID]] = $val[BOL_RateDao::SCORE];
        }

        foreach ( array_diff($entityIdList, array_keys($result)) as $id )
        {
            $result[$id] = 0;
        }

        return $result;
    }
}