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
 * The class is a gateway for auth. adapters and provides common API to authenticate users.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @method static OW_CacheManager getInstance()
 * @since 1.0
 */
class OW_CacheManager
{
    use OW_Singleton;
    
    const CLEAN_ALL = 'all';
    const CLEAN_OLD = 'old';
    const CLEAN_MATCH_TAGS = 'match_tag';
    const CLEAN_MATCH_ANY_TAG = 'match_any_tag';
    const CLEAN_NOT_MATCH_TAGS = 'not_match_tags';
    const TAG_OPTION_INSTANT_LOAD = 'base.tag_option.instant_load';

    /**
     * @var OW_ICacheBackend
     */
    private $cacheBackend;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var boolean
     */
    private $cacheEnabled;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->cacheEnabled = !false;
    }

    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled( $cacheEnabled )
    {
        $this->cacheEnabled = (bool) $cacheEnabled;
    }

    public function load( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->load($key);
        }

        return null;
    }

    public function test( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->test($key);
        }

        return false;
    }

    public function save( $data, $key, $tags = array(), $specificLifetime = false )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->save($data, $key, $tags, ($specificLifetime === false ? $this->lifetime : $specificLifetime));
        }

        return false;
    }

    public function remove( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->remove($key);
        }

        return false;
    }

    public function clean( $tags = array(), $mode = self::CLEAN_MATCH_ANY_TAG )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->clean($tags, $mode);
        }

        return false;
    }

    /**
     * @param OW_ICacheBackend $cacheBackend
     */
    public function setCacheBackend( OW_ICacheBackend $cacheBackend )
    {
        $this->cacheBackend = $cacheBackend;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime( $lifetime )
    {
        $this->lifetime = $lifetime;
    }

    private function cacheAvailable()
    {
        return $this->cacheBackend !== null && $this->cacheEnabled;
    }
}