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
 * @author Madumarov Sardar <madumarov@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_CacheBackendMysql implements OW_ICacheBackend
{
    /**
     * @var array
     */
    private $loadedItems;

    /**
     * @var OW_Database
     */
    private $dbo;

    public function __construct( OW_Database $dbo )
    {
        $this->dbo = $dbo;
        $result = $this->dbo->queryForList("SELECT `key`, `content` FROM `" . $this->getCacheTableName() . "` WHERE `expireTimestamp` >= :ct AND `instantLoad` = 1", array('ct' => time()));
        $this->loadedItems = array();

        foreach ( $result as $item )
        {
            $this->loadedItems[$item['key']] = $item['content'];
        }
    }

    public function clean( array $tags, $mode )
    {
        if ( !$tags || !$mode )
        {
            return false;
        }

        switch ( $mode )
        {
            case OW_CacheManager::CLEAN_ALL:
                $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "`");
                $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "`");
                break;

            case OW_CacheManager::CLEAN_MATCH_ANY_TAG:
                $this->dbo->delete("DELETE `c` FROM `" . $this->getCacheTableName() . "` AS `c` INNER JOIN `" . $this->getTagsTableName() . "` AS `t` ON ( `c`.`id` = `t`.`cacheId` ) WHERE `t`.`tag` IN ( " . $this->dbo->mergeInClause($tags) . " ) ");
                break;

            case OW_CacheManager::CLEAN_MATCH_TAGS:
                throw new LogicException("CLEAN_MATCH_TAGS hasn't been implemeted yet");
                //$cacheIds = $this->dbo->queryForColumnList("SELECT `` ");
                break;

            case OW_CacheManager::CLEAN_NOT_MATCH_TAGS:
                $this->dbo->delete("DELETE `c` FROM `" . $this->getCacheTableName() . "` AS `c` LEFT JOIN `" . $this->getTagsTableName() . "` AS `t` ON ( `c`.`id` = `t`.`cacheId` ) WHERE `t`.`tag` IS NULL OR `t`.`tag` NOT IN ( " . $this->dbo->mergeInClause($tags) . " ) ");
                break;

            case OW_CacheManager::CLEAN_OLD:
                $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `expireTimestamp` < :ctime", array('ctime' => time()));
                break;
        }

        $this->dbo->query("DELETE `t` FROM `" . $this->getTagsTableName() . "` AS `t` LEFT JOIN `" . $this->getCacheTableName() . "`  AS `c` on (`t`.`cacheId` = `c`.`id`) WHERE `c`.`id` IS NULL");
    }

    public function load( $key )
    {
        if ( isset($this->loadedItems[$key]) )
        {
            return $this->loadedItems[$key];
        }

        $result = $this->dbo->queryForColumn("SELECT `content` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key AND `expireTimestamp` >= :ts", array('key' => $key, 'ts' => time()));

        if ( $result )
        {
            return $result;
        }

        return null;
    }

    public function remove( $key )
    {
        if ( !$key )
        {
            return;
        }

        $result = $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key", array('key' => $key));

        if ( $result )
        {
            $result = intval($result);
        }
        else
        {
            return;
        }

        $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "` WHERE `cacheId` = :cacheId", array('cacheId' => $result));
        $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `id` = :id", array('id' => $result));
    }

    public function save( $data, $key, array $tags = array(), $lifeTime )
    {
        if ( empty($key) || empty($data) || empty($lifeTime) )
        {
            return;
        }

        $tags = array_unique($tags);
        $instantLoad = false;

        $optionIndex = array_search(OW_CacheManager::TAG_OPTION_INSTANT_LOAD, $tags);

        if ( $optionIndex !== false )
        {
            $instantLoad = true;
            unset($tags[$optionIndex]);
        }

        $expTime = time() + $lifeTime;

        $oldEntryId = $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key", array('key' => $key));

        if ( $oldEntryId !== null )
        {
            $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `id` = :id", array('id' => $oldEntryId));
            $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "` WHERE `cacheId` = :cacheId", array('cacheId' => $oldEntryId));
        }

        $this->dbo->query("INSERT INTO `" . $this->getCacheTableName() . "` (`key`, `content`, `expireTimestamp`, `instantLoad`) VALUES (:key, :content, :ts, :il)", array('key' => $key, 'content' => $data, 'ts' => $expTime, 'il' => $instantLoad));

        if ( $tags )
        {
            $cacheId = $this->dbo->getInsertId();
            foreach ( $tags as $tag )
            {
                $this->dbo->query("INSERT INTO `" . $this->getTagsTableName() . "` (`tag`, `cacheId`) VALUES (:tag, :cacheId)", array('tag' => $tag, 'cacheId' => $cacheId));
            }
        }
    }

    public function test( $key )
    {
        return $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key AND `expireTimestamp` >= :ts", array('key' => $key, 'ts' => time())) ? true : false;
    }

    private function getCacheTableName()
    {
        return OW_DB_PREFIX . 'base_cache';
    }

    private function getTagsTableName()
    {
        return OW_DB_PREFIX . 'base_cache_tag';
    }
}