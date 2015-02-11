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
 * @package ow_updates.classes
 * @since 1.0
 */
final class UPDATE_ConfigService
{
    /**
     * @var OW_Config
     */
    private $configManager;
    /**
     * @var UPDATE_ConfigService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UPDATE_ConfigService
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
        $this->configManager = OW_Config::getInstance();
    }

    /**
     * Returns config value for provided plugin key and config name.
     *
     * @param string $key
     * @param string $name
     * @return string|null
     */
    public function getValue( $key, $name )
    {
        return $this->configManager->getValue($key, $name);
    }

    /**
     * Adds plugin config.
     *
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function addConfig( $key, $name, $value, $descripton = null )
    {
        $this->configManager->addConfig($key, $name, $value, $descripton);
    }

    /**
     * Deletes config by provided plugin key and config name.
     *
     * @param string $key
     * @param string $name
     */
    public function deleteConfig( $key, $name )
    {
        $this->configManager->deleteConfig($key, $name);
    }

    /**
     * Checks if config exists.
     *
     * @param string $key
     * @param string $name
     * @return boolean
     */
    public function configExists( $key, $name )
    {
        return $this->configManager->configExists($key, $name);
    }

    /**
     * Updates config value.
     *
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function saveConfig( $key, $name, $value )
    {
        $this->configManager->saveConfig($key, $name, $value);
    }
}