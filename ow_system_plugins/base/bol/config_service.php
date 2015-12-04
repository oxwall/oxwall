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
 * Config service.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_ConfigService
{
    const EVENT_BEFORE_SAVE = "base.before_config_save";
    const EVENT_AFTER_SAVE = "base.after_config_save";
    
    const EVENT_BEFORE_REMOVE = "base.before_config_remove";
    const EVENT_AFTER_REMOVE = "base.after_config_remove";
    
    /**
     * @var BOL_ConfigDao
     */
    private $configDao;
    /**
     * @var BOL_ConfigService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ConfigService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->configDao = BOL_ConfigDao::getInstance();
    }

    /**
     * Returns config value for provided key and name.
     *
     * @param string $key
     * @param string $name
     * @return string
     */
    public function findConfigValue( $key, $name )
    {
        $config = $this->configDao->findConfig($key, $name);

        if ( $config === null )
        {
            return null;
        }

        return $config->getValue();
    }

    /**
     * Returns config item for provided key and name.
     * 
     * @param $key
     * @param $name
     * @return unknown_type
     */
    public function findConfig( $key, $name )
    {
        return $this->configDao->findConfig($key, $name);
    }

    /**
     * Returns config items list for provided plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function findConfigsList( $key )
    {
        return $this->configDao->findConfigsList($key);
    }

    /**
     * Returns all configs.
     *
     * @return array<BOL_Config>
     */
    public function findAllConfigs()
    {
        return $this->configDao->findAll();
    }

    /**
     * Adds new config item.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function addConfig( $key, $name, $value, $description = null )
    {
        if ( $this->findConfig($key, $name) !== null )
        {
            throw new InvalidArgumentException("Can't add config `" . $name . "` in section `" . $key . "`. Duplicated key and name!");
        }

        $newConfig = new BOL_Config();
        $newConfig->setKey($key)->setName($name)->setValue($value)->setDescription($description);
        
        $event = OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_SAVE, array(
            "key" => $key,
            "name" => $name,
            "value" => $value,
            "oldValue" => null
        ), $value));
        
        $newConfig->setValue($event->getData());
        
        $this->configDao->save($newConfig);
    }

    /**
     * Updates config item value.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function saveConfig( $key, $name, $value )
    {
        $config = $this->configDao->findConfig($key, $name);

        if ( $config === null )
        {
            throw new InvalidArgumentException("Can't find config `" . $name . "` in section `" . $key . "`!");
        }

        $event = OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_SAVE, array(
            "key" => $key,
            "name" => $name,
            "value" => $value,
            "oldValue" => $config->getValue()
        ), $value));
        
        $this->configDao->save($config->setValue($event->getData()));
        
        OW::getEventManager()->trigger(new OW_Event(self::EVENT_AFTER_SAVE, array(
            "key" => $key,
            "name" => $name,
            "value" => $value,
            "oldValue" => $config->getValue()
        )));
    }

    /**
     * Removes config item by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function removeConfig( $key, $name )
    {
        $event = OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_REMOVE, array(
            "key" => $key,
            "name" => $name
        )));
        
        if ( $event->getData() !== false )
        {
            $this->configDao->removeConfig($key, $name);
            
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_REMOVE, array(
                "key" => $key,
                "name" => $name
            )));
        }
    }

    /**
     * Removes all plugin configs.
     * 
     * @param string $pluginKey
     */
    public function removePluginConfigs( $pluginKey )
    {
        $this->configDao->removeConfigs($pluginKey);
    }
}