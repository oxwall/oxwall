<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.2
 */
class BOL_FileService
{
    /**
     * Singleton instance.
     *
     * @var BOL_FileService
     */
    private static $classInstance;

    /**
     * @var BOL_FileDao
     */
    private $fileDao;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->fileDao = BOL_FileDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_FileService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }
    
    public function getUploadErrorMessage( $errorCode )
    {
        $message = '';
        
        if ( !isset($errorCode) )
        {
            return false;
        }
        
        $language = OW::getLanguage();
        
        if ( $errorCode != UPLOAD_ERR_OK )
        {
            switch ( $errorCode )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $message = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $message = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $message = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    $message;

                case UPLOAD_ERR_CANT_WRITE:
                    $message = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $message = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $message = $language->text('base', 'upload_file_fail');
            }
        }

        return $message;
    }
    
    public function getUploadMaxFilesize() {
        $uploadMaxFilesize = (float) $this->getMegabytes(ini_get("upload_max_filesize"));
        $postMaxSize = (float) $this->getMegabytes(ini_get("post_max_size"));
        
        $maxUploadMaxFilesize = $uploadMaxFilesize >= $postMaxSize ? $postMaxSize : $uploadMaxFilesize;
        
        return $maxUploadMaxFilesize;
    }

    public function getUploadMaxFilesizeBytes( $convert = true )
    {
        $postMaxSize = trim(ini_get('post_max_size'));
        $uploadMaxSize = trim(ini_get('upload_max_filesize'));

        $lastPost = strtolower($postMaxSize[strlen($postMaxSize) - 1]);
        $lastUpload = strtolower($uploadMaxSize[strlen($uploadMaxSize) - 1]);

        $intPostMaxSize = (int)$postMaxSize;
        $intUploadMaxSize = (int)$uploadMaxSize;

        switch ( $lastPost )
        {
            case 'g': $intPostMaxSize *= 1024;
            case 'm': $intPostMaxSize *= 1024;
            case 'k': $intPostMaxSize *= 1024;
        }

        switch ( $lastUpload )
        {
            case 'g': $intUploadMaxSize *= 1024;
            case 'm': $intUploadMaxSize *= 1024;
            case 'k': $intUploadMaxSize *= 1024;
        }

        $possibleSize = array($postMaxSize => $intPostMaxSize, $uploadMaxSize => $intUploadMaxSize);
        return min($possibleSize);
    }
    
    /**
     *
     * @param string number of megabytes
     * @return float
     */
    private function getMegabytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
                break;
            case 'k':
                $val = $val/1024;
        }

        return $val;
    }

    /**
     * Counts all user uploaded files
     *
     * @param int $userId
     * @return int
     */
    public function countUserFiles( $userId )
    {
        return $this->fileDao->countUserFiles($userId);
    }

    /**
     * Adds file
     *
     * @param BOL_File $file
     * @return int
     */
    public function addPhoto( BOL_File $file )
    {
        $this->fileDao->save($file);

        return $file->id;
    }

    /**
     * Get file URL
     *
     * @param int $id
     *
     * @return string
     */
    public function getFileUrl( $id )
    {
        $userfilesUrl = OW::getPluginManager()->getPlugin('base')->getUserFilesUrl();
        $file = $this->fileDao->findById($id);
        return $userfilesUrl . $id . $file->filename;
    }

    /**
     * Get path to file in file system
     *
     * @param int $id
     *
     * @return string
     */
    public function getFilePath( $id )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir();
        $file = $this->fileDao->findById($id);
        return $userfilesDir . $id . $file->filename;
    }

    /**
     * Removes file
     *
     * @param int $id
     */
    public function removeFile( $id )
    {
        $path = $this->getFilePath($id);

        $storage = OW::getStorage();

        if ( $storage->fileExists($path) )
        {
            $storage->removeFile($path);
        }
    }

}
