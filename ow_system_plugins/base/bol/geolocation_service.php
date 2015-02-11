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
 * Geolocation Service
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_GeolocationService
{
    private static $classInstance;
    /**
     * @var boolean
     */
    private $isAvailable;
    /**
     * @var BOL_GeolocationCountryDao
     */
    private $countryDao;
    /**
     * @var BOL_GeolocationIpToCountryDao
     */
    private $ipCountryDao;

    /**
     *
     * @return BOL_GeolocationService 
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->countryDao = BOL_GeolocationCountryDao::getInstance();
        $this->ipCountryDao = BOL_GeolocationIpToCountryDao::getInstance();
        
        $this->isAvailable = $this->countryDao->doesTableExist();
    }

    public function ipToCountryCode3( $ip )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        return $this->ipCountryDao->ipToCountryCode3($ip);
    }

    public function getCountryNameListForCC3( array $codes )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countries = array();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $codes as $code )
        {
            $countries[$code] = $this->getCountryNameForCC3($code);
        }

        return $countries;
    }

    public function getAllCountryNameListForCC3()
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countryList = $this->countryDao->findAll();
        $countries = array();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $countryList as $country )
        {
            $countries[$country->cc3] = $this->getCountryNameForCC3($country->cc3);
        }

        return $countries;
    }

    public function getCountryNameForCC3( $code )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        return OW::getLanguage()->text('base', 'geolocation_country_name_' . $code);
    }

    public function isServiceAvailable()
    {
        return $this->isAvailable;
    }

    public function updateCountryNameListToLanguage( $languageId )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countryList = $this->countryDao->findAll();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $countryList as $country )
        {
            $key = BOL_LanguageService::getInstance()->findKey('base', 'geolocation_country_name_' . $country->cc3);
            if ( $key !== null )
            {
                $value = BOL_LanguageService::getInstance()->findValue($languageId, $key->id);
                if ( $value !== null )
                {
                    $value->value = ucwords(strtolower($country->name));
                    BOL_LanguageService::getInstance()->saveValue($value, false);
                }
            }
        }
    }
    // "LOAD DATA LOCAL INFILE '/home/nurlan/Downloads/ip-to-country.csv' INTO TABLE `ow_base_geolocation_ip2country` FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' (`ipFrom`, `ipTo`, `cc2`, `cc3`, `name`)";
    //insert into ow_base_geolocation_country (`cc2`, `cc3`, `name`) select `cc2`, `cc3`, `name` from ow_base_geolocation_ip2country group by `cc3`
}