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
 * Base session class.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_core
 * @method static OW_Session getInstance()
 * @since 1.0
 */
class OW_Session
{
    use OW_Singleton;
    
    private static $protectedKeys = array('session.home_url', 'session.user_agent');

    private function __construct()
    {
        if ( session_id() === '' )
        {
            //disable transparent sid support
            ini_set('session.use_trans_sid', '0');
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        }
    }

    public function getName()
    {
        return md5(OW_URL_HOME);
    }

    public function start()
    {
        //TODO: maybe session_destroy ?
        session_name($this->getName());

        $cookie = session_get_cookie_params();
        $cookie['httponly'] = true;

        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

        session_start();

        if ( !isset($_SESSION['session.home_url']) )
        {
            $_SESSION['session.home_url'] = OW_URL_HOME;
        }
        else if ( strcmp($_SESSION['session.home_url'], OW_URL_HOME) )
        {
            $this->regenerate();
        }

        $userAgent = OW::getRequest()->getUserAgentName();

        if ( isset($_SESSION['session.user_agent']) )
        {
            if ( $_SESSION['session.user_agent'] !== $userAgent )
            {
                $this->regenerate();
            }
        }
        else
        {
            $_SESSION['session.user_agent'] = $userAgent;
        }
    }

    public function regenerate()
    {
        session_regenerate_id();

        $_SESSION = array();

        if ( isset($_COOKIE[$this->getName()]) )
        {
            $_COOKIE[$this->getName()] = $this->getId();
        }
    }

    public function getId()
    {
        return session_id();
    }

    public function set( $key, $value )
    {
        if ( in_array($key, self::$protectedKeys) )
        {
            throw new Exception('Attempt to set protected key');
        }

        $_SESSION[$key] = $value;
    }

    public function get( $key )
    {
        if ( !isset($_SESSION[$key]) )
        {
            return null;
        }

        return $_SESSION[$key];
    }

    public function isKeySet( $key )
    {
        return isset($_SESSION[$key]);
    }

    public function delete( $key )
    {
        unset($_SESSION[$key]);
    }
}
