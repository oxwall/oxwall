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
     * Copies whole directory from source to destination folder. The destionation folder will be created if it doesn't exist.
     * Array and callable can be passed as filter argument. Array should contain the list of file extensions to be copied.
     * Callable is more flexible way for filtering, it should contain one parameter (file/dir to be copied) and return bool 
     * value which indicates if the item should be copied.
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param mixed $filter
     * @param int $level
     */
    public static function copyDir( $sourcePath, $destPath, $filter = null, $level = -1 )
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
            chmod($destPath, 0777);
        }

        $handle = opendir($sourcePath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === "." || $item === ".." )
                {
                    continue;
                }

                $path = $sourcePath . DS . $item;
                $dPath = $destPath . DS . $item;

                if ( is_callable($filter) && !call_user_func($filter, $path) )
                {
                    continue;
                }

                $copy = ($filter === null || (is_array($filter) && in_array(self::getExtension($item), $filter)) || is_callable($filter));

                if ( is_file($path) && $copy )
                {
                    copy($path, $dPath);
                    chmod($dPath, 0666);
                }
                else if ( $level && is_dir($path) )
                {
                    self::copyDir($path, $dPath, $filter, ($level - 1));
                }
            }

            closedir($handle);
        }
    }

    /**
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
     * @param string $filename
     * @param bool $humanReadable
     * @return int|string
     */
    public static function getFileSize( $filename, $humanReadable = true )
    {
        $bytes = filesize($filename);

        if ( !$humanReadable )
        {
            return $bytes;
        }

        return self::convertBytesToHumanReadable($bytes);
    }

    /**
     * Returns file extension
     *
     * @param string $filenName
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

        $specialChars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(",
            ")", "|", "~", "`", "!", "{", "}");
        $fileName = str_replace($specialChars, '', $fileName);
        $fileName = preg_replace('/[\s-]+/', '-', $fileName);
        $fileName = trim($fileName, '.-_');

        return $fileName;
    }

    /**
     * Checks if uploaded file is valid, if not returns localized error string.
     * 
     * @param int $errorCode
     * @return array
     */
    public static function checkUploadedFile( array $filesItem, $fileSizeLimitInBytes = null )
    {
        $language = OW::getLanguage();

        if ( empty($filesItem) || !array_key_exists("tmp_name", $filesItem) || !array_key_exists("size", $filesItem) )
        {
            return array("result" => false, "message" => $language->text("base", "upload_file_fail"));
        }

        if ( $fileSizeLimitInBytes == null )
        {
            $fileSizeLimitInBytes = self::getFileUploadServerLimitInBytes();
        }

        if ( $filesItem["error"] != UPLOAD_ERR_OK )
        {
            switch ( $filesItem["error"] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $errorString = $language->text("base", "upload_file_max_upload_filesize_error",
                        array("limit" => ($fileSizeLimitInBytes / 1024 / 1024) . "MB"));
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $errorString = $language->text("base", "upload_file_file_partially_uploaded_error");
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $errorString = $language->text("base", "upload_file_no_file_error");
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorString = $language->text("base", "upload_file_no_tmp_dir_error");
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $errorString = $language->text("base", "upload_file_cant_write_file_error");
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $errorString = $language->text("base", "upload_file_invalid_extention_error");
                    break;

                default:
                    $errorString = $language->text("base", "upload_file_fail");
            }

            return array("result" => false, "message" => $errorString);
        }

        if ( $filesItem['size'] > $fileSizeLimitInBytes )
        {
            return array("result" => false, "message" => $language->text("base",
                    "upload_file_max_upload_filesize_error",
                    array("limit" => ($fileSizeLimitInBytes / 1024 / 1024) . "MB")));
        }

        if ( !is_uploaded_file($filesItem["tmp_name"]) )
        {
            return array("result" => false, "message" => $language->text("base", "upload_file_fail"));
        }

        return array("result" => true);
    }

    /**
     * Returns server file upload limit in bytes
     * 
     * @return int
     */
    public static function getFileUploadServerLimitInBytes()
    {
        $uploadMaxFilesize = self::convertHumanReadableToBytes(ini_get("upload_max_filesize"));
        $postMaxSize = self::convertHumanReadableToBytes(ini_get("post_max_size"));

        return $uploadMaxFilesize < $postMaxSize ? $uploadMaxFilesize : $postMaxSize;
    }

    /**
     * Converts human readable (10Mb, 20Kb...) in bytes
     * 
     * @param string $value
     * @return int
     */
    public static function convertHumanReadableToBytes( $value )
    {
        $value = trim($value);
        $lastChar = strtolower($value[strlen($value) - 1]);
        $value = floatval($value);

        switch ( $lastChar )
        {
            case "g":
                $value *= 1024;
            case "m":
                $value *= 1024;
            case "k":
                $value *= 1024;
        }

        return intval($value);
    }

    /**
     * Converts bytes in human readable string
     * 
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public static function convertBytesToHumanReadable( $bytes, $decimals = 2 )
    {
        $size = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

        $factor = (int) floor((strlen($bytes) - 1) / 3);

        if ( isset($size[$factor]) )
        {
            return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . $size[$factor];
        }

        return $bytes;
    }
}
