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

namespace Oxwall\Core;

/**
 * Base Data Access Object class.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @since 1.8.3
 */
abstract class BaseDao
{
    const DEFAULT_CACHE_LIFETIME = false;

    public abstract function getTableName();

    public abstract function getDtoClassName();
    /**
     *
     * @var Database
     */
    protected $dbo;

    protected function __construct()
    {
        $this->dbo = \OW::getDbo();
    }

    /**
     * Finds and returns mapped entity item
     *
     * @param int $id
     * @return Entity
     */
    public function findById( $id, $cacheLifeTime = 0, $tags = array() )
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE `id` = ?";

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array((int) $id), $cacheLifeTime, $tags);
    }

    /**
     * Finds and returns mapped entity list
     *
     * @param array $idList
     * @return array
     */
    public function findByIdList( array $idList, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return array();
        }
        $sql = "SELECT * FROM {$this->getTableName()} WHERE `id` IN({$this->dbo->mergeInClause($idList)})";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * @param Example $example
     * @param int $cacheLifeTime
     * @param array $tags
     * @return array
     * @throws \InvalidArgumentException
     */
    public function findListByExample( $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new \InvalidArgumentException("Argument must not be null");
        }

        $sql = "SELECT * FROM {$this->getTableName()}{$example}";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * 
     * @param Example $example
     * @param int $cacheLifeTime
     * @param array $tags
     * @return int
     * @throws \InvalidArgumentException
     */
    public function countByExample( $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new \InvalidArgumentException("Argument must not be null");
        }

        $sql = "SELECT COUNT(*) FROM {$this->getTableName()}{$example}";

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    /**
     * @param Example $example
     * @param int $cacheLifeTime
     * @param array $tags
     * @return Entity
     * @throws \InvalidArgumentException
     */
    public function findObjectByExample( $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new \InvalidArgumentException("Argument must not be null");
        }

        $example->setLimitClause(0, 1);
        $sql = "SELECT * FROM {$this->getTableName()}{$example}";

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns all mapped entries of table
     *
     * @return array
     */
    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        $sql = "SELECT * FROM {$this->getTableName()}";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns count of all rows
     *
     * @return array
     */
    public function countAll()
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTableName()}";

        return $this->dbo->queryForColumn($sql);
    }

    /**
     * Delete entity by id. Returns affected rows
     * @param int $id
     * @return int
     */
    public function deleteById( $id )
    {
        $id = (int) $id;

        if ( $id > 0 )
        {
            $sql = "DELETE FROM {$this->getTableName()} WHERE `id` = ?";
            $result = $this->dbo->delete($sql, array($id));
            $this->clearCache();
            return $result;
        }

        return 0;
    }

    /**
     * Deletes list of entities by id list. Returns affected rows
     *
     * @param array $idList
     * @return int
     */
    public function deleteByIdList( array $idList )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return;
        }
        $sql = "DELETE FROM {$this->getTableName()} WHERE `id` IN({$this->dbo->mergeInClause($idList)})";

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    /**
     * @param Example $example
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteByExample( $example )
    {
        if ( $example === null || mb_strlen($example->__toString()) === 0 )
        {
            throw new \InvalidArgumentException("Example must not be null or empty");
        }
        $sql = "DELETE FROM {$this->getTableName()}{$example}";

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    /**
     * Saves and updates Entity item
     * @throws \InvalidArgumentException
     *
     * @param OW_Entity $entity
     * 
     * @throws InvalidArgumentException
     */
    public function save( $entity )
    {
        if ( $entity === null || !($entity instanceof Entity) )
        {
            throw new \InvalidArgumentException("Argument must be instance of OW_Entity and cannot be null");
        }

        $entity->id = (int) $entity->id;

        if ( $entity->id > 0 )
        {
            $this->dbo->updateObject($this->getTableName(), $entity);
        }
        else
        {
            $entity->id = NULL;
            $entity->id = $this->dbo->insertObject($this->getTableName(), $entity);
        }

        $this->clearCache();
    }

    public function saveDelayed( $entity )
    {
        if ( $entity === null || !($entity instanceof Entity) )
        {
            throw new \InvalidArgumentException("Argument must be instance of OW_Entity and cannot be null");
        }

        $entity->id = (int) $entity->id;

        if ( $entity->id > 0 )
        {
            $this->dbo->updateObject($this->getTableName(), $entity, 'id', true);
        }
        else
        {
            $entity->id = $this->dbo->insertObject($this->getTableName(), $entity, true);
        }

        $this->clearCache();
    }

    public function delete( $entity )
    {
        $this->deleteById($entity->id);
        $this->clearCache();
    }

    /**
     * @param Example $example
     * @param int $cacheLifeTime
     * @param array $tags
     * @return int
     * @throws \InvalidArgumentException
     */
    public function findIdByExample( $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new \InvalidArgumentException("Argument must not be null");
        }

        $example->setLimitClause(0, 1);
        $sql = "SELECT `id` FROM {$this->getTableName()}{$example}";

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    /**
     * @param Example $example
     * @param int $cacheLifeTime
     * @param array $tags
     * @return array
     * @throws \InvalidArgumentException
     */
    public function findIdListByExample( $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new \InvalidArgumentException("Argument must not be null");
        }

        $sql = "SELECT `id` FROM {$this->getTableName()}{$example}";

        return $this->dbo->queryForColumnList($sql, array(), $cacheLifeTime, $tags);
    }

    protected function clearCache()
    {
        $tagsToClear = $this->getClearCacheTags();

        if ( $tagsToClear )
        {
            \OW::getCacheManager()->clean($tagsToClear);
        }
    }

    /**
     * @return array
     */
    protected function getClearCacheTags()
    {
        return array();
    }

    protected function tableDataChanged()
    {
        
    }
}
