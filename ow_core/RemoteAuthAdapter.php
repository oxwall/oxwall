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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */

class RemoteAuthAdapter extends AuthAdapter 
{
    private $remoteId;
    private $type;
    
    /**
     * 
     * @var \BOL_RemoteAuthService
     */
    private $remoteAuthService;
    
    public function __construct($remoteId, $type)
    {
        $this->remoteId = $remoteId;
        $this->type = trim($type);
        
        $this->remoteAuthService = \BOL_RemoteAuthService::getInstance();
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getRemoteId()
    {
        return $this->remoteId;
    }
    
    public function isRegistered()
    {
        return $this->remoteAuthService->findByRemoteTypeAndId($this->type, $this->remoteId);
    }
    
    public function register( $userId, $custom = null )
    {
        $entity = new \BOL_RemoteAuth();
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
            $code = AuthResult::FAILURE;
        }
        else
        {
            $userId = (int) $entity->userId;
            $code = AuthResult::SUCCESS;
        }
          
        return new AuthResult($code, $userId);
    }
}