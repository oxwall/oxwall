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
 * Tag Service class.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_TagService
{
    const CONFIG_DEFAULT_TAGS_COUNT = 'tags_count';
    const CONFIG_MIN_FONT_SIZE = 'min_font_size';
    const CONFIG_MAX_FONT_SIZE = 'max_font_size';

    /**
     * @var array
     */
    private $configs = array();
    /**
     * @var BOL_TagDao
     */
    private $tagDao;
    /**
     * @var BOL_EntityTagDao
     */
    private $entityTagDao;
    /**
     * Singleton instance.
     * 
     * @var BOL_TagService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_TagService
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
     *
     */
    private function __construct()
    {
        $this->tagDao = BOL_TagDao::getInstance();
        $this->entityTagDao = BOL_EntityTagDao::getInstance();
        $this->configs[self::CONFIG_DEFAULT_TAGS_COUNT] = 20;
        $this->configs[self::CONFIG_MIN_FONT_SIZE] = 10;
        $this->configs[self::CONFIG_MAX_FONT_SIZE] = 30;
    }

    /**
     * Returns config value.
     * 
     * @param string $name
     * @return mixed
     */
    public function getConfig( $name )
    {
        if ( isset($this->configs[$name]) )
        {
            return $this->configs[$name];
        }

        return null;
    }

    /**
     * Returns configs array.
     * 
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Saves and updates tag entries.
     * 
     * @param BOL_Tag $tagItem
     */
    public function saveTag( BOL_Tag $tagItem )
    {
        $this->tagDao->save($tagItem);
    }

    /**
     * Saves and updates entity_tag entries.
     * 
     * @param BOL_EntityTag $entityTagItem
     */
    public function saveEntityTag( BOL_EntityTag $entityTagItem )
    {
        $this->entityTagDao->save($entityTagItem);
    }

    /**
     * Adds tag list to entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @param array $tags
     */
    public function updateEntityTags( $entityId, $entityType, array $tags )
    {
        $tags = array_map('htmlspecialchars', $tags);
        $tags = array_map('mb_strtolower', $tags);

        $tags = $this->updateTagList($tags);

        $entityTags = $this->findEntityTags($entityId, $entityType);

        $tagsToAdd = array_udiff($tags, $entityTags, array($this, 'tagDiff'));

        /* @var $tag BOL_Tag */
        foreach ( $tagsToAdd as $tag )
        {
            $entityTagItem = new BOL_EntityTag();
            $entityTagItem->setEntityId($entityId)->setEntityType($entityType)->setTagId($tag->getId());
            $this->entityTagDao->save($entityTagItem);
        }

        $tagsToDelete = array_udiff($entityTags, $tags, array($this, 'tagDiff'));

        foreach ( $tagsToDelete as $tag )
        {
            $this->entityTagDao->deleteEntityTagItem($entityId, $entityType, $tag->getId());
        }
    }

    /**
     * Returns tag list for provided entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return array<BOL_Tag>
     */
    public function findEntityTags( $entityId, $entityType )
    {
        $entityTags = $this->entityTagDao->findEntityTagItems($entityId, $entityType);

        $tagIds = array();

        /* @var $entityTag BOL_EntityTag */
        foreach ( $entityTags as $entityTag )
        {
            $tagIds[] = $entityTag->getTagId();
        }

        return $this->tagDao->findByIdList($tagIds);
    }

    public function findTagListByEntityIdList( $entityType, array $idList )
    {
        $tagInfo = $this->tagDao->findTagListByEntityIdList($entityType, $idList);

        $resultArray = array();

        foreach ( $tagInfo as $info )
        {
            $resultArray[$info['entityId']][] = $info['label'];
        }

        foreach ( $idList as $id )
        {
            if ( !isset($resultArray[$id]) )
            {
                $resultArray[$id] = array();
            }
        }

        return $resultArray;
    }

    /**
     * Returns tag list with popularity for provided entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagsWithPopularity( $entityId, $entityType )
    {
        return $this->tagDao->findEntityTagsWithPopularity($entityId, $entityType);
    }

    /**
     * Unlinks all entity item's tags.
     * 
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityTags( $entityId, $entityType )
    {
        $this->entityTagDao->deleteItemsForEntityItem($entityId, $entityType);
    }

    /**
     * Returns list of the most popular tags for entity type.
     * 
     * @param string $entityType
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function findMostPopularTags( $entityType, $limit, $offset  = 0 )
    {
        return $this->tagDao->findMostPopularTags($entityType, $limit, $offset);
    }

    /**
     * Adds new tags to global tag list.
     * Returns tag dto for every provided tag label.
     * If tag has 
     * 
     * @param array<string> $tagList
     * @return array<BOL_Tag>
     */
    public function updateTagList( $tagList )
    {
        $tagList = $tagList; // TODO add bad words filter

        foreach ( $tagList as $key => $value )
        {
            // TODO badwords filter + add to construction below


            if ( trim($value) === '' || false )
            {
                unset($tagList[$key]);
                continue;
            }

            $tagList[$key] = $value; //TODO add process (remove html tags and  not allowed symbols)
        }

        $tagList = array_unique($tagList);

        $tagsInDb = empty($tagList) ? array() : $this->tagDao->findTagsByLabel($tagList);

        $tagsInDbLabels = array();

        /* @var $value BOL_Tag */
        foreach ( $tagsInDb as $value )
        {
            $tagsInDbLabels[] = $value->getLabel();
        }

        if ( sizeof($tagList) !== sizeof($tagsInDb) )
        {
            foreach ( $tagList as $value )
            {
                if ( !in_array($value, $tagsInDbLabels) )
                {
                    $newTag = new BOL_Tag();
                    $newTag->setLabel($value);

                    $this->tagDao->save($newTag);

                    $tagsInDb[] = $newTag;
                }
            }
        }

        return $tagsInDb;
    }

    /**
     * Don't call! Class auxilery method.
     * 
     * @param BOL_Entity $a
     * @param BOL_Entity $b
     * @return integer
     */
    public function tagDiff( $a, $b )
    {
        if ( $a->getId() === $b->getId() )
        {
            return 0;
        }
        else
        {
            if ( $a->getId() > $b->getId() )
            {
                return 1;
            }
        }

        return -1;
    }

    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $status = $status ? 1 : 0;

        $this->entityTagDao->updateEntityStatus($entityType, $entityId, $status);
    }

    public function findEntityListByTag( $entityType, $tag, $first, $count )
    {
        return $this->entityTagDao->findEntityListByTag($entityType, mb_strtolower($tag), $first, $count);
    }

    public function findEntityCountByTag( $entityType, $tag )
    {
        return $this->entityTagDao->findEntityCountByTag($entityType, mb_strtolower($tag));
    }

    public function deleteEntityTypeTags( $entityType )
    {
        $this->entityTagDao->deleteByEntityType($entityType);
    }
    
    public function updateEntityItemStatus( $entityType, $entityId, $status = true )
    {
        $this->entityTagDao->updateEntityStatus($entityType, (int)$entityId, (int)$status);
    }
}