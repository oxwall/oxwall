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
 * Profiler utility.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_Profiler
{
    /**
     * @var array
     */
    private static $classInstances;

    /**
     * @var int
     */
    private $checkPoints;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $result;

    /**
     * @var integer
     */
    private $chkCounter;

    /**
     * Returns profiler result array
     * 
     * @return array
     */
    public function getResult()
    {
        $this->stop();
        return $this->result;
    }

    /**
     * Returns total time past from the start.
     *
     * @return float
     */
    public function getTotalTime()
    {
        return (microtime(true) - $this->checkPoints['start']);
    }

    /**
     * Constructor
     *
     * @param string
     */
    private function __construct( $key )
    {
        $this->key = $key;
        $this->reset();
    }

    /**
     * Returns "single-tone" instance of class for every $key
     *
     * @param string $key #Profiler object identifier#
     * @return UTIL_Profiler
     */
    public static function getInstance( $key = '_ow_' )
    {
        if ( self::$classInstances === null )
        {
            self::$classInstances = array();
        }

        if ( !isset(self::$classInstances[$key]) )
        {
            self::$classInstances[$key] = new self($key);
        }

        return self::$classInstances[$key];
    }

    /**
     * Sets new profiler checkpoint
     *
     * @param string $key
     */
    public function mark( $key = null )
    {
        $this->checkPoints[( $key === null ? 'chk' . $this->chkCounter++ : $key)] = microtime(true);
    }

    /**
     * Stops profiler and geberates result array
     */
    private function stop()
    {
        $this->result['marks'] = array();

        foreach ( $this->checkPoints as $key => $value )
        {
            $this->result['marks'][$key] = sprintf('%.3f', $value - $this->checkPoints['start']);
        }

        $endMark = $this->result['marks']['end'] = sprintf('%.3f', microtime(true) - $this->checkPoints['start']);

        $this->result['total'] = $endMark;
    }

    /**
     * Resets profiler
     *
     */
    public function reset()
    {
        $this->checkPoints = array();
        $this->checkPoints['start'] = microtime(true);
        $this->result = array();
        $this->chkCounter = 0;
    }
}