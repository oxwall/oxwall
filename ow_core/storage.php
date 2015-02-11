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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */

interface OW_Storage
{
    const EVENT_ON_FILE_UPLOAD = 'cloud.on_file_upload';
    const EVENT_ON_FILE_DELETE = 'cloud.on_file_delete';

     /**
     * Copy dir to storage
     *
     * @param string $sourcePath
     * @param string $destPath
     * @param array $fileTypes
     * @param int $level
     *
     * @return boolean
     */
    public function copyDir ( $sourcePath, $destPath, array $fileTypes = null, $level = -1 );

     /**
     * Copy file to storage
     *
     * @param string $sourcePath
     * @param string $destPath
     *
     * @return boolean
     */
    public function copyFile ( $sourcePath, $destPath );

     /**
     * Copy file to local file system
     *
     * @param string $destPath
     * @param string $toFilePath
     *
     * @return boolean
     */
    public function copyFileToLocalFS ( $destPath, $toFilePath );

     /**
     * Return storage file content
     *
     * @param string $destPath
     *
     * @return string
     */
    public function fileGetContent ( $destPath );

     /**
     * Set storage file content
     *
     * @param string $destPath
     * @param string $content
     *
     * @return boolean
     */
    public function fileSetContent ( $destPath, $conent );

     /**
     * Remove storage dir
     *
     * @param string $destPath
     *
     * @return boolean
     */
    public function removeDir ( $destPath );

     /**
     * Remove storage file
     *
     * @param string $destPath
     *
     * @return boolean
     */
    public function removeFile ( $destPath );

     /**
     * Return file storage file
     *
     * @param string $path
     * @param string $prefix
     * @param array $fileTypes
     *
     * @return array
     */
    public function getFileNameList ( $path, $prefix = null, array $fileTypes = null );

     /**
     * Return file url
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileUrl ( $path );

     /**
     * Checks whether a file or directory exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function fileExists ( $path );

     /**
     * Tells whether the $path is a regular file
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isFile ( $path );

     /**
     * Tells whether the $path is a directory
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isDir ( $path );

     /**
     * Create directory
     *
     * @param string $path
     *
     * @return boolean
     */
    public function mkdir ( $path );
    
     /**
     * Tells whether the filename is writable
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isWritable ( $filename );

     /**
     * Rename file
     *
     * @param string $oldPath
     * @param string $newPath
     *
     * @return boolean
     */
    public function renameFile ( $oldPath, $newPath );

    /**
     * Rename file
     *
     * @param string $destPath
     * @param string $premissions
     *
     * @return boolean
     */
    public function chmod ( $destPath, $premissions );
}

?>
