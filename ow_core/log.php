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
 * @package ow_core
 * @since 1.0
 */
class OW_Log
{
    const TYPE = 'type';
    const KEY = 'key';
    const TIME_STAMP = 'timeStamp';
    const MESSAGE = 'message';

    /**
     * Class instances.
     *
     * @var array
     */
    private static $classInstances;

    /**
     * Returns logger object.
     *
     * @param string $type
     * @return OW_Log
     */
    public static function getInstance( $type )
    {
        if ( self::$classInstances === null )
        {
            self::$classInstances = array();
        }

        if ( empty(self::$classInstances[$type]) )
        {
            self::$classInstances[$type] = new self($type);
        }

        return self::$classInstances[$type];
    }
    /**
     * Log type.
     *
     * @var string
     */
    private $type;
    /**
     * Log entries.
     *
     * @var array
     */
    private $entries = array();
    /**
     * @var OW_LogWriter
     */
    private $logWriter;

    /**
     * Constructor.
     *
     * @param string $type
     * @param OW_LogWriter $logWriter
     */
    private function __construct( $type )
    {
        $this->type = $type;
        $this->logWriter = new BASE_CLASS_DbLogWriter();
        OW::getEventManager()->bind('core.exit', array($this, 'writeLog'));
        OW::getEventManager()->bind('core.emergency_exit', array($this, 'writeLog'));
    }

    /**
     * Adds log entry.
     *
     * @param string $message
     * @param string $key
     */
    public function addEntry( $message, $key = null )
    {
        $this->entries[] = array(self::TYPE => $this->type, self::KEY => $key, self::MESSAGE => $message, self::TIME_STAMP => time());        
    }

    /**
     * Returns all log entries.
     * 
     * @return array
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Sets log writer.
     *
     * @param OW_LogWriter $logWriter
     */
    public function setLogWriter( OW_LogWriter $logWriter )
    {
        $this->logWriter = $logWriter;
    }

    /**
     * 
     */
    public function writeLog()
    {
        if ( $this->logWriter !== null && !empty($this->entries))
        {
            $this->logWriter->processEntries($this->entries);
            $this->entries = array();
        }
    }
}