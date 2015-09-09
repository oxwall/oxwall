<?php

/**
 * Oxwall: Open Source Community Software
 * @copyright Skalfa LLC Copyright (C) 2009. All rights reserved.
 * @license CPAL 1.0 License - http://www.oxwall.org/license
 */

/**
 * Data Access Object for `base_remote_auth` table.
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow.base.bol
 * @since 1.0
 */

class BOL_RemoteAuthDao extends OW_BaseDao
{
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Singleton instance.
     *
     * @var BOL_RemoteAuthDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RemoteAuthDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_RemoteAuth';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_remote_auth';
    }
    
    /**
     * 
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByRemoteId( $remoteId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('remoteId', $remoteId);
        
        return $this->findObjectByExample($example); 
    }
    
    /**
     * 
     * @param $type
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByRemoteTypeAndId( $type, $remoteId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('remoteId', $remoteId);
        $example->andFieldEqual('type', $type);
        
        return $this->findObjectByExample($example);
    }
    
    /**
     * 
     * @param $userId
     * @return BOL_RemoteAuth
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->findObjectByExample($example); 
    }
    
    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example); 
    }
    
    public function saveOrUpdate( BOL_RemoteAuth $entity )
    {
        $example = new OW_Example();
        $example->andFieldEqual('remoteId', $entity->remoteId);
        $example->andFieldEqual('userId', $entity->userId);
        
        $entityDto = $this->findObjectByExample($example);
        if ( $entityDto !== null )
        {
            $entity = $entityDto;
        }
        
        return $this->save($entity);
    }
}