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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_File
{
    /**
     * Avaliable image extensions
     *
     * @var array
     */
    private static $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');

    /**
     * Avaliable video extensions
     *
     * @var array
     */
    private static $videoExtensions = array('avi', 'mpeg', 'wmv', 'flv', 'mov', 'mp4');

    /**
     * Enter description here...
     *
     * @param unknown_type $sourcePath
     * @param unknown_type $destPath
     */
    public static function copyDir( $sourcePath, $destPath, array $fileTypes = null, $level = -1 )
    {
        $sourcePath = self::removeLastDS($sourcePath);

        $destPath = self::removeLastDS($destPath);

        if ( !self::checkDir($sourcePath) )
        {
            return;
        }

        if ( !file_exists($destPath) )
        {
            mkdir($destPath);
        }

        $handle = opendir($sourcePath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $sourcePath . DS . $item;
                $dPath = $destPath . DS . $item;

                if ( is_file($path) && ( $fileTypes === null || in_array(self::getExtension($item), $fileTypes) ) )
                {
                    copy($path, $dPath);
                }
                else if ( $level && is_dir($path) )
                {
                    self::copyDir($path, $dPath, $fileTypes, ($level - 1));
                }
            }

            closedir($handle);
        }
    }

    /**
     *
     * @param string $dirPath
     * @param array $fileTypes
     * @param integer $level
     * @return array
     */
    public static function findFiles( $dirPath, array $fileTypes = null, $level = -1 )
    {
        $dirPath = self::removeLastDS($dirPath);

        $resultList = array();

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( is_file($path) && ( $fileTypes === null || in_array(self::getExtension($item), $fileTypes) ) )
                {
                    $resultList[] = $path;
                }
                else if ( $level && is_dir($path) )
                {
                    $resultList = array_merge($resultList, self::findFiles($path, $fileTypes, ($level - 1)));
                }
            }

            closedir($handle);
        }

        return $resultList;
    }

    /**
     * Removes directory with content
     *
     * @param string $dirPath
     * @param boolean $empty
     */
    public static function removeDir( $dirPath, $empty = false )
    {
        $dirPath = self::removeLastDS($dirPath);

        if ( !self::checkDir($dirPath) )
        {
            return;
        }

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( is_file($path) )
                {
                    unlink($path);
                }
                else if ( is_dir($path) )
                {
                    self::removeDir($path);
                }
            }

            closedir($handle);
        }

        if ( $empty === false )
        {
            if ( !rmdir($dirPath) )
            {
                trigger_error("Cant remove directory `" . $dirPath . "`!", E_USER_WARNING);
            }
        }
    }

    /**
     * Returns file extension
     *
     * @param string $filename
     * @return string
     */
    public static function getExtension( $filenName )
    {
        return strtolower(substr($filenName, (strrpos($filenName, '.') + 1)));
    }

    /**
     * Rteurns filename with stripped extension
     *
     * @param string $fileName
     * @return string
     */
    public static function stripExtension( $fileName )
    {
        if ( !strstr($fileName, '.') )
        {
            return trim($fileName);
        }

        return substr($fileName, 0, (strrpos($fileName, '.')));
    }

    /**
     * Returns path without last directory separator
     *
     * @param string $path
     * @return string
     */
    public static function removeLastDS( $path )
    {
        $path = trim($path);

        if ( substr($path, -1) === DS )
        {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    public static function checkDir( $path )
    {
        if ( !file_exists($path) || !is_dir($path) )
        {
            //trigger_warning("Cant find directory `".$path."`!");

            return false;
        }

        if ( !is_readable($path) )
        {
            //trigger_warning('Cant read directory `'.$path.'`!');

            return false;
        }

        return true;
    }
    /* NEED to be censored */

    /**
     * Validates file
     *
     * @param string $fileName
     * @param array $avalia
     * bleExtensions
     * @return bool
     */
    public static function validate( $fileName, array $avaliableExtensions = array() )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        if ( empty($avaliableExtensions) )
        {
            $avaliableExtensions = array_merge(self::$imageExtensions, self::$videoExtensions);
        }

        $extension = self::getExtension($fileName);

        return in_array($extension, $avaliableExtensions);
    }

    /**
     * Validates image file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateImage( $fileName )
    {
        return self::validate($fileName, self::$imageExtensions);
    }

    /**
     * Validates video file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateVideo( $fileName )
    {
        return self::validate($fileName, self::$videoExtensions);
    }

    /**
     * Sanitizes a filename, replacing illegal characters
     *
     * @param string $fileName
     * @return string
     */
    public static function sanitizeName( $fileName )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        $specialChars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
        $fileName = str_replace($specialChars, '', $fileName);
        $fileName = preg_replace('/[\s-]+/', '-', $fileName);
        $fileName = trim($fileName, '.-_');

        return $fileName;
    }
    
    /**
     *
     * @param int $uploadErrorCode
     * @return string
     */
    public static function getErrorMessage( $uploadErrorCode )
    {
        if ( !isset($uploadErrorCode) )
        {
            return false;
        }

        $message = '';
        
        if ( $uploadErrorCode != UPLOAD_ERR_OK )
        {
            switch ( $uploadErrorCode )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }

            OW::getFeedback()->error($error);
            $this->redirect();
        }

        return $fileName;
    }
}