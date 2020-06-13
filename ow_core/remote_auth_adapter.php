<?php

/**
 * Class OW_RemoteAuthAdapter
 */
class OW_RemoteAuthAdapter extends OW_AuthAdapter
{
    private $remoteId;
    private $type;
    
    /**
     * 
     * @var BOL_RemoteAuthService
     */
    private $remoteAuthService;

    /**
     * OW_RemoteAuthAdapter constructor.
     * @param int $remoteId
     * @param string $type
     */
    public function __construct($remoteId, $type)
    {
        $this->remoteId = $remoteId;
        $this->type = trim($type);
        
        $this->remoteAuthService = BOL_RemoteAuthService::getInstance();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getRemoteId()
    {
        return $this->remoteId;
    }

    /**
     * @return BOL_RemoteAuth
     */
    public function isRegistered()
    {
        return $this->remoteAuthService->findByRemoteTypeAndId($this->type, $this->remoteId);
    }

    /**
     * @param int $userId
     * @param string|null $custom
     */
    public function register( $userId, $custom = null )
    {
        $entity = new BOL_RemoteAuth();
        $entity->userId = (int) $userId;
        $entity->remoteId = $this->remoteId;
        $entity->type = $this->type;
        $entity->timeStamp = time();
        $entity->custom = $custom;

        return $this->remoteAuthService->saveOrUpdate($entity);
    }
    
    /**
     *
     * @return OW_AuthResult
     */
    public function authenticate()
    {
        $entity = $this->remoteAuthService->findByRemoteTypeAndId($this->type, $this->remoteId);
        
        if ( $entity === null )
        {
            $userId = null;
            $code = OW_AuthResult::FAILURE;
        }
        else
        {
            $userId = (int) $entity->userId;
            $code = OW_AuthResult::SUCCESS;
        }
          
        return new OW_AuthResult($code, $userId);
    }
}