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
 * The class replaces standard PHP error/exception handlers with custom ones,
 * allowing better error management.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
final class UPDATE_ErrorManager
{
    /**
     * Singleton instance.
     *
     * @var OW_ErrorManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_ErrorManager
     */
    public static function getInstance( $debugMode = true )
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self($debugMode);
        }

        return self::$classInstance;
    }
    /**
     * @var boolean
     */
    private $debugMode;
    /**
     * @var string
     */
    private $errorPageUrl;

    /**
     * Constructor.
     */
    private function __construct( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;

        // set custom error and exception interceptors
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));

        // set error reporting level
        error_reporting(-1);
    }

    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param boolean $debugMode
     */
    public function setDebugMode( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;
    }

    /**
     * @return string
     */
    public function getErrorPageUrl()
    {
        return $this->errorPageUrl;
    }

    /**
     * @param string $errorPageUrl
     */
    public function setErrorPageUrl( $errorPageUrl )
    {
        $this->errorPageUrl = $errorPageUrl;
    }

    /**
     * Custom error handler.
     *
     * @param integer $errno
     * @param string $errString
     * @param string $errFile
     * @param integer $errLine
     * @return boolean
     */
    public function errorHandler( $errno, $errString, $errFile, $errLine )
    {
        // ignore if line is prefixed by `@`
        if ( error_reporting() === 0 )
        {
            return true;
        }

        $data = array(
            'message' => $errString,
            'file' => $errFile,
            'line' => $errLine
        );

        switch ( $errno )
        {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT;
                $data['type'] = 'Notice';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_CORE_WARNING:
                $data['type'] = 'Warning';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            default:
                $data['type'] = 'Error';

                if ( $this->debugMode )
                {
                    $this->handleDie($data);
                }
                else
                {
                    $this->handleRedirect($data);
                }
                break;
        }

        return true;
    }

    /**
     * Custom exception handler.
     *
     * @param Exception $e
     */
    public function exceptionHandler( Exception $e )
    {
        $data = array(
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => '<pre>' . $e->getTraceAsString() . '</pre>',
            'type' => 'Exception',
            'class' => get_class($e)
        );

        if ( $this->debugMode )
        {
            $this->handleDie($data);
        }
        else
        {
            $this->handleRedirect($data);
        }
    }

    private function handleShow( $data )
    {
        UTIL_Debug::printDebugMessage($data);
    }

    private function handleDie( $data )
    {
        UTIL_Debug::printDebugMessage($data);
        exit;
    }

    private function handleRedirect( $data )
    {
//        header("HTTP/1.1 500 Internal Server Error");
//        header('Location: ' . OW_URL_HOME . '500.phtml');
    }

    private function handleIgnore( $data )
    {
        return;
    }
}
