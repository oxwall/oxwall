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
class UTIL_Ftp
{
    const ERROR_FTP_FUNCTION_IS_NOT_AVAILABLE = 'error_ftp_function_is_not_available';
    const ERROR_EMPTY_HOST_PROVIDED = 'error_empty_host_provided';
    const ERROR_CANT_CONNECT_TO_HOST = 'error_cant_connect_to_host';
    const ERROR_EMPTY_CREDENTIALS_PROVIDED = 'error_empty_credentials_provided';
    const ERROR_INVALID_CREDENTIALS_PROVIDED = 'error_invalid_credentials_provided';

    /**
     * @var connection stream
     */
    private $stream;

    /**
     * @var connection timeout
     */
    private $timeout = 30;

    /**
     * @var boolean
     */
    private $loggedIn = false;

    /**
     * FTP root dir.
     *
     * @var string
     */
    private $ftpRootDir;

    /**
     * Constructor.
     *
     * @param array $options
     */
    private function __construct()
    {
        
    }

    public function init()
    {
        $dirRoot = OW_DIR_ROOT;

        if ( substr($dirRoot, 0, 1) === DS )
        {
            $dirRoot = substr($dirRoot, 1);
        }

        if ( substr($dirRoot, -1) === DS )
        {
            $dirRoot = substr($dirRoot, 0, -1);
        }

        $pathList = array($dirRoot);

        while ( true )
        {
            $dirRoot = substr($dirRoot, ( strpos($dirRoot, DS) + 1));
            $pathList[] = $dirRoot;
            if ( !strstr($dirRoot, DS) )
            {
                break;
            }
        }


        foreach ( $pathList as $path )
        {
            if ( $this->isFtpRootDir($path) )
            {
                $this->ftpRootDir = substr(OW_DIR_ROOT, 0, strpos(OW_DIR_ROOT, $path));
                break;
            }
        }

        if ( $this->ftpRootDir === null )
        {
            $this->ftpRootDir = OW_DIR_ROOT;
        }

        $this->chdir('/');

        $dirname = "temp" . rand(1, 1000000);
        if ( ftp_mkdir($this->stream, $dirname) )
        {
            // hotfix, doesn't work with win servers
            $rootPath = "";

            $dirRootPathArr = array_filter(explode(DS, OW_DIR_ROOT));
            array_unshift($dirRootPathArr, "");

            foreach ( $dirRootPathArr as $pathItem )
            {
                $rootPath .= $pathItem . DS;

                if ( file_exists($rootPath . $dirname) )
                {
                    $this->ftpRootDir = $rootPath;

                    ftp_rmdir($this->stream, $dirname);
                    return;
                }
            }
        }
    }

    /**
     * @param array $params
     * @return UTIL_Ftp
     */
    public static function getConnection( array $params )
    {
        if ( !function_exists('ftp_connect') )
        {
            throw new LogicException(self::ERROR_FTP_FUNCTION_IS_NOT_AVAILABLE);
        }

        if ( empty($params['host']) )
        {
            throw new InvalidArgumentException(self::ERROR_EMPTY_HOST_PROVIDED);
        }

        if ( empty($params['login']) || empty($params['password']) )
        {
            throw new InvalidArgumentException(self::ERROR_EMPTY_CREDENTIALS_PROVIDED);
        }

        $connection = new self();

        if ( !empty($params['timeout']) )
        {
            $connection->setTimeout((int) $params['timeout']);
        }

        if ( !$connection->connect(trim($params['host']), (!empty($params['port']) ? (int) $params['port'] : 21)) )
        {
            throw new LogicException(self::ERROR_CANT_CONNECT_TO_HOST);
        }

        if ( !$connection->login(trim($params['login']), trim($params['password'])) )
        {
            throw new LogicException(self::ERROR_INVALID_CREDENTIALS_PROVIDED);
        }

        $connection->init();

        return $connection;
    }

    private function isFtpRootDir( $path )
    {
        $this->chdir('/');
        $segments = array_filter(explode(DS, $path));
        foreach ( $segments as $segment )
        {
            if ( !@$this->chdir($segment) )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout( $timeout )
    {
        $this->timeout = (int) $timeout;
    }

    public function connect( $host, $port = 21 )
    {
        if ( is_resource($this->stream) )
        {
            return true;
        }

        $this->stream = ftp_connect($host, $port, $this->timeout);
        return (!empty($this->stream) && is_resource($this->stream));
    }

    public function isConnected()
    {
        return is_resource($this->stream);
    }

    public function login( $username, $password )
    {
        if ( ftp_login($this->stream, $username, $password) )
        {
            $this->loggedIn = true;
        }

        return $this->loggedIn;
    }

    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    public function pwd()
    {
        return ftp_pwd($this->stream);
    }

    public function chdir( $path )
    {
        $path = $this->getPath($path);
        return ftp_chdir($this->stream, $path);
    }

    public function rename( $fromPath, $toPath )
    {
        $fromPath = $this->getPath($fromPath);
        $toPath = $this->getPath($toPath);

        return ftp_rename($this->stream, $fromPath, $toPath);
    }

    public function delete( $filePath )
    {
        $filePath = $this->getPath($filePath);

        return ftp_delete($this->stream, $filePath);
    }

    public function rmDir( $dirPath )
    {
        $dirPath = $this->getPath($dirPath);

        $dirPath = UTIL_File::removeLastDS($dirPath);

        $dirContent = $this->readDir($dirPath);

        foreach ( $dirContent as $dirItem )
        {
            if ( $dirItem['type'] === 'file' )
            {
                $this->delete($dirPath . DS . $dirItem['name']);
            }
            else
            {
                $this->rmDir($dirPath . DS . $dirItem['name']);
            }
        }

        ftp_rmdir($this->stream, $dirPath);
    }

    public function mkDir( $dirPath )
    {
        $dirPath = $this->getPath($dirPath);

        $result = ftp_mkdir($this->stream, $dirPath);

        if ( $result === false )
        {
            trigger_error("Can't create dir by FTP `" . $dirPath . "`", E_USER_WARNING);
        }
    }

    public function readDir( $dirPath, $type = 'all' )
    {
        $dirPath = $this->getPath($dirPath);

        if ( ftp_pasv($this->stream, true) === false )
        {
            trigger_error("Can't set passive mode for FTP connection", E_USER_WARNING);
        }

        $resultArray = array();
        $list = ftp_rawlist($this->stream, $dirPath);

        if ( $list === false )
        {
            trigger_error("Can't get dir contents `" . $dirPath . "`", E_USER_WARNING);
            return array();
        }

        if ( empty($list[0]) )
        {
            return $resultArray;
        }

        if ( strtolower(substr($list[0], 0, 6)) === 'total ' )
        {
            array_shift($list);

            if ( empty($list[0]) )
            {
                return $resultArray;
            }
        }

        switch ( strtolower(ftp_systype($this->stream)) )
        {
            case 'unix':
                $pattern = '/([-dl][rwxstST-]+).* ([0-9]*) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9])[ ]+(([0-9]{1,2}:[0-9]{2})|[0-9]{4}) (.+)/';

                foreach ( $list as $item )
                {
                    $tempRegs = array();
                    $tempArray = null;

                    if ( preg_match($pattern, $item, $tempRegs) )
                    {
                        $tempArray = array(
                            'type' => ( strpos("-dl", substr($tempRegs[1], 0, 1)) ? 'dir' : 'file' ),
                            'rights' => $tempRegs[1],
                            'user' => $tempRegs[3],
                            'group' => $tempRegs[4],
                            'size' => $tempRegs[5],
                            'date' => $tempRegs[6],
                            'time' => $tempRegs[7],
                            'name' => $tempRegs[9]
                        );
                    }

                    if ( !empty($tempArray) && $tempArray['name'] !== '.' && $tempArray['name'] !== '..' && ( $type === 'all' || $type === $tempArray['type'] ) )
                    {
                        $resultArray[] = $tempArray;
                    }
                }

                break;

            case 'mac':
                $pattern = '([-dl][rwxstST-]+).* ?([0-9 ]*)?([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9])[ ]+(([0-9]{2}:[0-9]{2})|[0-9]{4}) (.+)';
                break;

            case 'win':
                $pattern = '([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)';
                break;
        }

        return $resultArray;
    }

    public function chmod( $mode, $filePath )
    {
        $filePath = $this->getPath($filePath);

        $result = ftp_chmod($this->stream, $mode, $filePath);

        if ( $result === false )
        {
            throw new LogicException("Can't chmod by FTP `" . $filePath . "`");
        }
    }

    public function upload( $localFile, $remoteFile )
    {
        $remoteFile = $this->getPath($remoteFile);

        if ( !ftp_put($this->stream, $remoteFile, $localFile, FTP_BINARY) )
        {
            trigger_error("Can't upload file by FTP `" . $localFile . "`", E_USER_WARNING);
        }
    }

    /**
     * Uploads LOCAL DIR CONTENTS to remote dir.
     * 
     * @param string $localDir
     * @param string $remoteDir     
     */
    public function uploadDir( $localDir, $remoteDir )
    {
        $remoteDir = $this->getPath($remoteDir);

        if ( !file_exists($localDir) )
        {
            trigger_error("Can't read dir `" . $localDir . "`!", E_USER_WARNING);
            return;
        }

        if ( !file_exists($remoteDir) )
        {
            $this->mkDir($remoteDir);
        }

        $handle = opendir($localDir);

        while ( ($item = readdir($handle)) !== false )
        {
            if ( $item === '.' || $item === '..' )
            {
                continue;
            }

            $localPath = $localDir . DS . $item;
            $remotePath = $remoteDir . DS . $item;

            if ( is_file($localPath) )
            {
                $this->upload($localPath, $remotePath);
            }
            else
            {
                $this->uploadDir($localPath, $remotePath);
            }
        }

        closedir($handle);
    }

    public function download( $remoteFile, $localFile )
    {
        $remoteFile = $this->getPath($remoteFile);

        if ( ftp_get($this->stream, $localFile, $remoteFile, FTP_BINARY) )
        {
            throw new LogicException("Can't download file by FTP `" . $remoteFile . "`");
        }
    }

    public function __destruct()
    {
        ftp_close($this->stream);
    }

    private function getPath( $path )
    {
        if ( $this->ftpRootDir === null )
        {
            return $path;
        }

        if ( strpos($path, $this->ftpRootDir) !== 0 )
        {
            return $path;
        }

        if ( strlen($path) != strlen($this->ftpRootDir) )
        {
            $path = substr($path, strlen($this->ftpRootDir));
        }

        return $path;
    }
}
