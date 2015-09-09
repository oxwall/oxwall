<?php

class BOL_RemoteAuthService
{
    /**
     * 
     * @var BOL_RemoteAuthDao
     */
    private $remoteAuthDao;
    
    private function __construct()
    {
        $this->remoteAuthDao = BOL_RemoteAuthDao::getInstance();
    }

    /**
     * Class instance
     *
     * @var BOL_RemoteAuthService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_RemoteAuthService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    /**
     * 
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByRemoteId( $remoteId )
    {
        return $this->remoteAuthDao->findByRemoteId($remoteId);
    }
    
    /**
     * 
     * @param $type
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByRemoteTypeAndId( $type, $remoteId )
    {
        return $this->remoteAuthDao->findByRemoteTypeAndId($type, $remoteId);
    }
    
    /**
     * 
     * @param $userId
     * @return BOL_RemoteAuth
     */
    public function findByUserId( $userId  )
    {
        return $this->remoteAuthDao->findByUserId($userId);
    }
    
    public function saveOrUpdate( BOL_RemoteAuth $entity )
    {
        return $this->remoteAuthDao->saveOrUpdate($entity);
    }
    
    public function deleteByUserId( $userId )
    {
        return $this->remoteAuthDao->deleteByUserId($userId);
    }
}