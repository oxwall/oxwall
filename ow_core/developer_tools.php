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
 * @method static OW_DeveloperTools getInstance()
 * @since 1.8.3
 */
class OW_DeveloperTools
{
    const CACHE_ENTITY_TEMPLATE = 2;
    const CACHE_ENTITY_THEME = 4;
    const CACHE_ENTITY_LANGUAGE = 8;
    const CACHE_ENTITY_PLUGIN_STRUCTURE = 32;
    const EVENT_UPDATE_CACHE_ENTITIES = "base.update_cache_entities";
    const CONFIG_NAME = "dev_mode";

    use OW_Singleton;
    
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * @var BOL_ThemeService
     */
    private $themeService;

    /**
     * @var BOL_LanguageService
     */
    private $languageService;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->pluginService = BOL_PluginService::getInstance();
        $this->themeService = BOL_ThemeService::getInstance();
        $this->languageService = BOL_LanguageService::getInstance();
    }

    public function init()
    {
        $configDev = (int) OW::getConfig()->getValue("base", self::CONFIG_NAME);

        if ( $configDev > 0 )
        {
            $this->refreshEntitiesCache($configDev);
            OW::getConfig()->saveConfig("base", self::CONFIG_NAME, 0);
            OW::getApplication()->redirect();
        }

        if ( defined("OW_DEV_MODE") && OW_DEV_MODE )
        {
            $this->refreshEntitiesCache(OW_DEV_MODE);
        }

        // show profiler only for desktop and if it's enabled
        if ( !OW::getApplication()->isDesktop() || OW::getRequest()->isAjax() || !OW_PROFILER_ENABLE )
        {
            return;
        }

        OW_View::setCollectDevInfo(true);
        OW::getEventManager()->setDevMode(true);
        OW::getEventManager()->bind("base.append_markup", array($this, "onAppendMarkup"));
    }
    /* ---------------------------------------- Developer handlers -------------------------------------------------- */

    /**
     * Updates all entity types cache
     * 
     * @param int $options
     */
    public function refreshEntitiesCache( $options = 1 )
    {
        $options = (intval($options) == 1 ? PHP_INT_MAX : intval($options));

        if ( $options & self::CACHE_ENTITY_TEMPLATE )
        {
            $this->clearTemplatesCache();
        }

        if ( $options & self::CACHE_ENTITY_THEME )
        {
            $this->clearThemeCache();
        }

        if ( $options & self::CACHE_ENTITY_LANGUAGE )
        {
            $this->clearLanguagesCache();
        }

        if ( $options & self::CACHE_ENTITY_PLUGIN_STRUCTURE )
        {
            $this->updateStructureforAllPlugins();
        }

        OW::getEventManager()->trigger(new OW_Event(self::EVENT_UPDATE_CACHE_ENTITIES, array("options" => $options)));
    }

    /**
     * Updates all templates cache
     */
    public function clearTemplatesCache()
    {
        OW_ViewRenderer::getInstance()->clearCompiledTpl();
    }

    /**
     * Updates themes list and regenerates cache of each theme
     */
    public function clearThemeCache()
    {
        $this->themeService->updateThemeList();
        $this->themeService->processAllThemes();

        if ( OW::getConfig()->configExists("base", "cachedEntitiesPostfix") )
        {
            OW::getConfig()->saveConfig("base", "cachedEntitiesPostfix", UTIL_String::getRandomString());
        }
    }

    /**
     * Updates cache for all languages
     */
    public function clearLanguagesCache()
    {
        $this->languageService->generateCacheForAllActiveLanguages();
    }

    /**
     * Updates dir structure for all plugins
     */
    public function updateStructureforAllPlugins()
    {
        $plugins = $this->pluginService->findAllPlugins();

        /* @var $pluginDto BOL_Plugin */
        foreach ( $plugins as $pluginDto )
        {
            $this->pluginService->addPluginDirs($pluginDto);
        }
    }

    /* ----------------------- Event handlers ----------------------------------------------------------------------- */

    /**
     * The method collects all the developer info during the page handling.
     * 
     * @param BASE_CLASS_EventCollector $event
     */
    public function onAppendMarkup( BASE_CLASS_EventCollector $event )
    {
        $viewRenderer = OW_ViewRenderer::getInstance();
        $viewRenderer->assignVar("oxwall", BOL_StorageService::getInstance()->getPlatformXmlInfo());

        $view = new OW_View();
        $view->setTemplate(OW::getPluginManager()->getPlugin("base")->getCmpViewDir() . "dev_tools_tpl.html");

        // get current request attributes
        $requestHandlerData = OW::getRequestHandler()->getDispatchAttributes();

        try
        {
            $ctrlPath = OW::getAutoloader()->getClassPath($requestHandlerData["controller"]);
        }
        catch ( Exception $e )
        {
            $ctrlPath = "not_found";
        }

        $requestHandlerData["ctrlPath"] = $ctrlPath;
        $requestHandlerData["paramsExp"] = var_export(( empty($requestHandlerData["params"]) ? array() : $requestHandlerData["params"]),
            true);

        $view->assign("requestHandler", $requestHandlerData);

        // get current request memory usage
        $memoryUsage = "No info";

        if ( function_exists("memory_get_peak_usage") )
        {
            $memoryUsage = UTIL_File::convertBytesToHumanReadable(memory_get_peak_usage(true));
        }

        $view->assign("memoryUsage", $memoryUsage);

        // get default profiler data
        $view->assign("profiler", UTIL_Profiler::getInstance()->getResult());

        // rendered view data
        $view->assign("renderedItems", $this->getViewInfo(OW_View::getDevInfo()));

        // sql queries data
        $filter = !empty($_GET["pr_query_log_filter"]) ? trim($_GET["pr_query_log_filter"]) : null;
        $view->assign("database",
            $this->getSqlInfo(OW::getDbo()->getQueryLog(), OW::getDbo()->getTotalQueryExecTime(), $filter));

        // events data
        $view->assign("events", $this->getEventInfo(OW::getEventManager()->getLog()));

        $view->assign("clrBtnUrl", OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor("BASE_CTRL_Base", "turnDevModeOn"),
            array("back-uri" => urlencode(OW::getRouter()->getUri()))));

        $event->add($view->render());
    }

    protected function getSqlInfo( array $sqlData, $totalTime, $queryFilter = null )
    {
        foreach ( $sqlData as $key => $query )
        {
            if ( $queryFilter )
            {
                if ( !mb_strstr($query["query"], $queryFilter) )
                {
                    unset($sqlData[$key]);
                    continue;
                }
            }

            if ( isset($query["params"]) && is_array($query["params"]) )
            {
                $sqlData[$key]["params"] = var_export($query["params"], true);
            }
        }

        return array("qet" => $totalTime, "ql" => $sqlData, "qc" => count($sqlData));
    }

    protected function getEventInfo( array $eventsData )
    {
        $eventsDataArray = array("bind" => array(), "calls" => array());

        foreach ( $eventsData["bind"] as $eventName => $listeners )
        {
            $eventsDataArray["bind"][] = array(
                "name" => $eventName,
                "listeners" => $this->getEventListeners($listeners)
            );
        }

        foreach ( $eventsData["call"] as $eventItem )
        {
            $paramsData = var_export($eventItem["event"]->getParams(), true);

            $eventsDataArray["call"][] = array(
                "type" => $eventItem["type"],
                "name" => $eventItem["event"]->getName(),
                "listeners" => $this->getEventListeners($eventItem["listeners"]),
                "params" => $paramsData,
                "start" => sprintf("%.3f", $eventItem["start"]),
                "exec" => sprintf("%.3f", $eventItem["exec"])
            );
        }

        $eventsDataArray["bindsCount"] = count($eventsDataArray["bind"]);
        $eventsDataArray["callsCount"] = count($eventsDataArray["call"]);

        return $eventsDataArray;
    }

    protected function getEventListeners( array $eventData )
    {
        $listenersList = array();

        foreach ( $eventData as $priority )
        {
            foreach ( $priority as $listener )
            {
                if ( is_array($listener) )
                {
                    if ( is_object($listener[0]) )
                    {
                        $listener = get_class($listener[0]) . " -> {$listener[1]}";
                    }
                    else
                    {
                        $listener = "{$listener[0]} :: {$listener[1]}";
                    }
                }
                else if ( is_string($listener) )
                {
                    // nothing to do
                }
                else
                {
                    $listener = "ClosureObject";
                }

                $listenersList[] = $listener;
            }
        }

        return $listenersList;
    }

    protected function getViewInfo( array $viewData )
    {
        $viewDataArray = array("mp" => array(), "cmp" => array(), "ctrl" => array());

        foreach ( $viewData as $class => $item )
        {
            try
            {
                $src = OW::getAutoloader()->getClassPath($class);
            }
            catch ( Exception $e )
            {
                $src = "not_found";
            }

            $addItem = array("class" => $class, "src" => $src, "tpl" => $item);

            if ( is_subclass_of($class, OW_MasterPage::class) )
            {
                $viewDataArray["mp"] = $addItem;
            }
            else if ( is_subclass_of($class, OW_ActionController::class) )
            {
                $viewDataArray["ctrl"] = $addItem;
            }
            else if ( is_subclass_of($class, OW_Component::class) )
            {
                $viewDataArray["cmp"][] = $addItem;
            }
            else
            {
                $viewDataArray["view"][] = $addItem;
            }
        }

        return array("items" => $viewDataArray, "count" => ( count($viewData) - 2 ));
    }
}
