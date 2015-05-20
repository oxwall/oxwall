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
     * Constructor.
     *
     */
    private function __construct()
    {

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
}
