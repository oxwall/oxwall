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
 * Temporary File Service Class.
 * 
 * @authors Sergei Kiselev <arrserg@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 * 
 */
final class BOL_FileTemporaryService
{
    CONST TEMPORARY_FILE_LIVE_LIMIT = 86400;
    
    /**
     * @var BOL_FileTemporaryDao
     */
    private $fileTemporaryDao;
    /**
     * Class instance
     *
     * @var BOL_FileTemporaryService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->fileTemporaryDao = BOL_FileTemporaryDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return BOL_FileTemporaryService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function addTemporaryFile( $source, $filename, $userId, $order = 0 )
    {
        if ( !file_exists($source) || !$userId )
        {
            return FALSE;
        }
        
        $tmpFile = new BOL_FileTemporary();
        $tmpFile->filename = $filename;
        $tmpFile->userId = $userId;
        $tmpFile->addDatetime = time();
        $tmpFile->order = $order;
        $this->fileTemporaryDao->save($tmpFile);

        $storage = OW::getStorage();
        $storage->copyFile($source, $this->fileTemporaryDao->getTemporaryFilePath($tmpFile->id));

        return $tmpFile->id;
    }

    public function findUserTemporaryFiles( $userId, $orderBy = 'timestamp' )
    {
        $list = $this->fileTemporaryDao->findByUserId($userId, $orderBy);
        
        $result = array();
        if ( $list )
        {
            foreach ( $list as $file )
            {
                $result[$file->id]['dto'] = $file;
                $result[$file->id]['src'] = $this->fileTemporaryDao->getTemporaryFileUrl($file->id, 1);
            }
        }
        
        return $result;
    }
    
    public function deleteUserTemporaryFiles( $userId )
    {
        $list = $this->fileTemporaryDao->findByUserId($userId);
        
        if ( !$list )
        {
            return true;
        }

        foreach ( $list as $file )
        {
            @unlink($this->fileTemporaryDao->getTemporaryFilePath($file->id));
            $this->fileTemporaryDao->delete($file);
        }

        return true;
    }
    
    public function deleteTemporaryFile( $fileId )
    {
        $file = $this->fileTemporaryDao->findById($fileId);
        if ( !$file )
        {
            return false;
        }

        @unlink($this->fileTemporaryDao->getTemporaryFilePath($fileId));
        $this->fileTemporaryDao->delete($file);
        
        return true;
    }
    
    public function deleteLimitedFiles()
    {   
        foreach ( $this->fileTemporaryDao->findLimitedFiles(self::TEMPORARY_FILE_LIVE_LIMIT) as $id )
        {
            $this->deleteTemporaryFile($id);
        }
    }

    public function moveTemporaryFile( $tmpId, $desc )
    {
        $tmp = $this->fileTemporaryDao->findById($tmpId);

        if ( !$tmp )
        {
            return FALSE;
        }

        $tmpFilePath = $this->fileTemporaryDao->getTemporaryFilePath($tmp->id);

        $fileService = BOL_FileService::getInstance();

        $file = new BOL_File();
        $file->description = htmlspecialchars(trim($desc));
        $file->addDatetime = time();
        $file->filename = $tmp->filename;
        $file->userId = $tmp->userId;
        BOL_FileDao::getInstance()->save($file);

        try
        {
            $storage = OW::getStorage();
            $storage->copyFile($tmpFilePath, $fileService->getFilePath($file->id));
        }
        catch ( Exception $e )
        {
            $photo = NULL;
        }

        return $file;
    }
}