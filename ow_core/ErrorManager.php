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
 * The class replaces standard PHP error/exception handlers with custom ones,
 * allowing better error management.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.0
 */
class ErrorManager
{
    /**
     * Singleton instance.
     *
     * @var ErrorManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ErrorManager
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
     * @var \BASE_CLASS_ErrOutput
     */
    private $errorOutput;

    /**
     * @var Log 
     */
    private $logger;

    /**
     * Constructor.
     */
    private function __construct( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;

        // set custom error and exception interceptors
        set_error_handler(array($this, "errorHandler"));
        set_exception_handler(array($this, "exceptionHandler"));

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
     * @return Log
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger( $logger )
    {
        $this->logger = $logger;
    }

    /**
     * @return \BASE_CLASS_ErrOutput
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * @param \BASE_CLASS_ErrOutput $errorOutput
     */
    public function setErrorOutput( $errorOutput )
    {
        $this->errorOutput = $errorOutput;
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
            "message" => $errString,
            "file" => $errFile,
            "line" => $errLine
        );

        //temp fix
        $e_depricated = defined("E_DEPRECATED") ? E_DEPRECATED : 0;

        switch ( $errno )
        {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case $e_depricated:

                $data["type"] = "Notice";

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
                $data["type"] = "Warning";

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
                $data["type"] = "Error";

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
    public function exceptionHandler( \Exception $e )
    {
        $data = array(
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "trace" => $e->getTraceAsString(),
            "type" => "Exception",
            "class" => get_class($e)
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
        $this->errorOutput->printString($data);
        $this->handleLog($data);
    }

    private function handleDie( $data )
    {
        $this->errorOutput->printString($data);
        $this->handleLog($data);

        \OW::getEventManager()->trigger(new \OW_Event("core.emergency_exit", $data));
        exit;
    }

    private function handleRedirect( $data )
    {
        $this->handleLog($data);
        \OW::getEventManager()->trigger(new \OW_Event("core.emergency_exit", $data));

        header("HTTP/1.1 500 Internal Server Error");
        header("Location: " . OW_URL_HOME . "e500.php");
    }

    private function handleIgnore( $data )
    {
        $this->handleLog($data);
        return;
    }

    private function handleLog( $data )
    {
        if ( $this->logger === null )
        {
            return;
        }

        $trace = !empty($data["trace"]) ? " Trace: [" . str_replace(PHP_EOL, " | ", $data["trace"]) . "]" : "";
        $message = "Message: " . $data["message"] . " File: " . $data["file"] . " Line:" . $data["line"] . $trace;
        $this->logger->addEntry($message, $data["type"]);
    }

    public function debugBacktrace( )
    {
        $stack = "";
        $i = 1;
        $trace = debug_backtrace();
        unset($trace[0]);

        foreach ( $trace as $node )
        {
            $stack .=  "#$i " . (isset($node["file"]) ? $node["file"] : "") . (isset($node["line"]) ? "(" . $node["line"] . "): " : "");
            if ( isset($node["class"]) )
            {
                $stack .= $node["class"] . "->";
            }
            $stack .= $node["function"] . "()" . PHP_EOL;
            $i++;
        }

        return $stack;
    }
}
