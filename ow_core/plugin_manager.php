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
 * The class is responsible for plugin management.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_core
 * @method static OW_PluginManager getInstance()
 * @since 1.0
 */
class OW_PluginManager
{
    use OW_Singleton;
    
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * @var array
     */
    private $cachedObjects = array();

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->pluginService = BOL_PluginService::getInstance();
    }

    /**
     * Returns active plugin object.
     *
     * @param string $key
     * @return OW_Plugin
     */
    public function getPlugin( $key )
    {
        $plugin = $this->pluginService->findPluginByKey($key);

        if ( $plugin === null || !$plugin->isActive() )
        {
            throw new InvalidArgumentException("There is no active plugin with key `{$key}`!");
        }

        if ( !array_key_exists($plugin->getKey(), $this->cachedObjects) )
        {
            $this->cachedObjects[$plugin->getKey()] = new OW_Plugin($plugin);
        }

        return $this->cachedObjects[$plugin->getKey()];
    }

    /**
     * Includes init script for all active plugins
     */
    public function initPlugins()
    {
        $plugins = $this->pluginService->findActivePlugins();

        usort($plugins,
            function( BOL_Plugin $a, BOL_Plugin $b )
        {
            if ( $a->getId() == $b->getId() )
            {
                return 0;
            }

            return ($a->getId() > $b->getId()) ? 1 : -1;
        });

        /* @var $value BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( !array_key_exists($plugin->getKey(), $this->cachedObjects) )
            {
                $this->cachedObjects[$plugin->getKey()] = new OW_Plugin($plugin);
            }

            $this->initPlugin($this->cachedObjects[$plugin->getKey()]);
        }
    }

    /**
     * Includes init script for provided plugin
     */
    public function initPlugin( OW_Plugin $pluginObject )
    {
        $this->addPackagePointers($pluginObject->getDto());

        $initDirPath = $pluginObject->getRootDir();

        if ( OW::getApplication()->getContext() == OW::CONTEXT_MOBILE )
        {
            $initDirPath = $pluginObject->getMobileDir();
        }
        if ( OW::getApplication()->getContext() == OW::CONTEXT_CLI )
        {
            $initDirPath = $pluginObject->getCliDir();
        }
        else if ( OW::getApplication()->getContext() == OW::CONTEXT_API )
        {
            $initDirPath = $pluginObject->getApiDir();
        }

        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "plugin_init.start", "pluginKey" => $pluginObject->getKey())));

        $this->pluginService->includeScript($initDirPath . BOL_PluginService::SCRIPT_INIT);

        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "plugin_init.end", "pluginKey" => $pluginObject->getKey())));
    }

    /**
     * Adds platform predefined package pointers
     * 
     * @param BOL_Plugin $pluginDto
     */
    public function addPackagePointers( BOL_Plugin $pluginDto )
    {
        $plugin = new OW_Plugin($pluginDto);
        $upperedKey = mb_strtoupper($plugin->getKey());
        $autoloader = OW::getAutoloader();

        $predefinedPointers = array(
            "CMP" => $plugin->getCmpDir(),
            "CTRL" => $plugin->getCtrlDir(),
            "BOL" => $plugin->getBolDir(),
            "CLASS" => $plugin->getClassesDir(),
            "MCMP" => $plugin->getMobileCmpDir(),
            "MCTRL" => $plugin->getMobileCtrlDir(),
            "MBOL" => $plugin->getMobileBolDir(),
            "MCLASS" => $plugin->getMobileClassesDir(),
            "ACTRL" => $plugin->getApiCtrlDir(),
            "ABOL" => $plugin->getApiBolDir(),
            "ACLASS" => $plugin->getApiClassesDir()
        );

        foreach ( $predefinedPointers as $pointer => $dirPath )
        {
            $autoloader->addPackagePointer($upperedKey . "_" . $pointer, $dirPath);
        }
    }

    /**
     * Update active plugins list for manager.
     * 
     * @deprecated since version 1.7.4
     */
    public function readPluginsList()
    {
        
    }

    /**
     * Returns plugin key for provided module name, works only for active plugins
     *
     * @param string $moduleName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getPluginKey( $moduleName )
    {
        $plugins = $this->pluginService->findActivePlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( $plugin->getModule() == $moduleName )
            {
                return $plugin->getKey();
            }
        }

        throw new InvalidArgumentException("There is no plugin with module name `{$moduleName}`!");
    }

    /**
     * Returns module name for provided plugin key
     *
     * @param string $pluginKey
     * @return string
     * @throws InvalidArgumentException
     */
    public function getModuleName( $pluginKey )
    {
        $plugin = $this->pluginService->findPluginByKey($pluginKey);

        if ( $plugin == null )
        {
            throw new InvalidArgumentException("There is no active plugin with key `{$key}`");
        }

        return $plugin->getModule();
    }

    /**
     * Checks if plugin is active
     *
     * @param string $pluginKey
     * @return boolean
     */
    public function isPluginActive( $pluginKey )
    {
        $plugin = $this->pluginService->findPluginByKey($pluginKey);

        return $plugin !== null && $plugin->isActive();
    }

    /**
     * Sets admin settings page route name for provided plugin
     *
     * @param string $pluginKey
     * @param string $routeName
     */
    public function addPluginSettingsRouteName( $pluginKey, $routeName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($pluginKey));

        if ( $plugin !== null )
        {
            $plugin->setAdminSettingsRoute($routeName);
            $this->pluginService->savePlugin($plugin);
        }
    }

    /**
     * Sets uninstall page route name for provided plugin
     *
     * @param string $key
     * @param string $routName
     */
    public function addUninstallRouteName( $key, $routName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($key));

        if ( $plugin !== null )
        {
            $plugin->setUninstallRoute($routName);
            $this->pluginService->savePlugin($plugin);
        }
    }

    /**
     * @param string $filePath
     */
    private function includeFile( $filePath )
    {
        if ( file_exists($filePath) )
        {
            include_once $filePath;
        }
    }
}
