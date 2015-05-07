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
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_DeveloperTools
{

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->context = self::CONTEXT_DESKTOP;
    }
    /**
     * Singleton instance.
     *
     * @var OW_Developer
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_Developer
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function refreshEntitiesCache( $options )
    {
        $options = intval($options);

        if ( $options === 1 || $options & 1 << 1 )
        {
            $this->clearTemplatesCache();
        }

        if ( $options === 1 || $options & 1 << 2 )
        {
            $this->clearThemeCache();
        }

        if ( $options === 1 || $options & 1 << 3 )
        {
            $this->clearLanguagesCache();
        }

        if ( $options === 1 || $options & 1 << 4 )
        {
            $this->clearDbCache();
        }

        if ( ( $options === 1 || $options & 1 << 5 ) && !defined('OW_PLUGIN_XP') )
        {
            
        }
    }

    public function clearTemplatesCache()
    {
        OW_ViewRenderer::getInstance()->clearCompiledTpl();
    }

    public function clearThemeCache()
    {
        BOL_ThemeService::getInstance()->updateThemeList();
        BOL_ThemeService::getInstance()->processAllThemes();

        if ( OW::getConfig()->configExists('base', 'cachedEntitiesPostfix') )
        {
            OW::getConfig()->saveConfig('base', 'cachedEntitiesPostfix', uniqid());
        }

        $event = new OW_Event('base.update_cache_entities');
        OW::getEventManager()->trigger($event);
    }

    public function clearLanguagesCache()
    {
        BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
    }

    public function clearDbCache()
    {
        OW::getCacheManager()->clean(array(), OW_CacheManager::CLEAN_ALL);
    }

    public function refreshPluginDirs()
    {
        $pluginService = BOL_PluginService::getInstance();
        $activePlugins = $pluginService->findActivePlugins();
        new OW_Plugin($params);
        /* @var $pluginDto BOL_Plugin */
        foreach ( $activePlugins as $pluginDto )
        {







            $pluginStaticDir = OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'static' . DS;

            if ( file_exists($pluginStaticDir) )
            {
                $staticDir = OW_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

                if ( !file_exists($staticDir) )
                {
                    mkdir($staticDir);
                    chmod($staticDir, 0777);
                }

                UTIL_File::copyDir($pluginStaticDir, $staticDir);
            }
        }
    }

    protected function devActions()
    {
//        if ( isset($_GET['capc']) && function_exists('apc_clear_cache') )
//        {
//            apc_clear_cache();
//            $this->redirect();
//        }

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        if ( OW::getUser()->isAdmin() )
        {
            //TODO add clear smarty cache
            //TODO add clear themes cache
            //TODO add clear db cache
        }

        $configDev = (int) OW::getConfig()->getValue('base', 'dev_mode');

        if ( $configDev > 0 )
        {
            $this->updateCachedEntities($configDev);
            OW::getConfig()->saveConfig('base', 'dev_mode', 0);
            $this->redirect();
        }

        if ( OW_PROFILER_ENABLE )
        {
            //get data for developer tool
            OW_Renderable::setDevMode(true);
            OW::getEventManager()->setDevMode(true);

            function base_dev_tool( BASE_CLASS_EventCollector $event )
            {
                $viewRenderer = OW_ViewRenderer::getInstance();
                $prevVars = $viewRenderer->getAllAssignedVars();
                $viewRenderer->assignVar('oxwall', (array) (simplexml_load_file(OW_DIR_ROOT . 'ow_version.xml')));
                $requestHandlerData = OW::getRequestHandler()->getDispatchAttributes();

                try
                {
                    $ctrlPath = OW::getAutoloader()->getClassPath($requestHandlerData['controller']);
                }
                catch ( Exception $e )
                {
                    $ctrlPath = 'not_found';
                }

                $requestHandlerData['ctrlPath'] = $ctrlPath;
                $requestHandlerData['paramsExp'] = var_export(( empty($requestHandlerData['params']) ? array() : $requestHandlerData['params']), true);
                $viewRenderer->assignVar('requestHandler', $requestHandlerData);
                $viewRenderer->assignVar('profiler', UTIL_Profiler::getInstance()->getResult());
                $viewRenderer->assignVar('memoryUsage', (function_exists('memory_get_peak_usage') ? sprintf('%0.3f', memory_get_peak_usage(true) / 1048576) : 'No info'));

                if ( !OW_DEV_MODE || true )
                { //TODO remove hardcode
                    $viewRenderer->assignVar('clrBtnUrl', OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('BASE_CTRL_Base', 'turnDevModeOn'), array('back-uri' => urlencode(OW::getRouter()->getUri()))));
                }

                $rndItems = OW_Renderable::getRenderedClasses();
                $rndArray = array('mp' => array(), 'cmp' => array(), 'ctrl' => array());
                foreach ( $rndItems as $key => $item )
                {
                    try
                    {
                        $src = OW::getAutoloader()->getClassPath($key);
                    }
                    catch ( Exception $e )
                    {
                        $src = 'not_found';
                    }

                    $addItem = array('class' => $key, 'src' => $src, 'tpl' => $item);

                    if ( strstr($key, 'OW_MasterPage') )
                    {
                        $rndArray['mp'] = $addItem;
                    }
                    else if ( strstr($key, '_CTRL_') )
                    {
                        $rndArray['ctrl'] = $addItem;
                    }
                    else
                    {
                        $rndArray['cmp'][] = $addItem;
                    }
                }

                $viewRenderer->assignVar('renderedItems', array('items' => $rndArray, 'count' => ( count(OW_Renderable::getRenderedClasses()) - 2 )));

                $queryLog = OW::getDbo()->getQueryLog();
                foreach ( $queryLog as $key => $query )
                {
                    if ( isset($_GET['pr_query_log_filter']) && strlen($_GET['pr_query_log_filter']) > 3 )
                    {
                        if ( !strstr($query['query'], $_GET['pr_query_log_filter']) )
                        {
                            unset($queryLog[$key]);
                            continue;
                        }
                    }

                    if ( isset($query['params']) && is_array($query['params']) )
                    {
                        $queryLog[$key]['params'] = var_export($query['params'], true);
                    }
                }

                $viewRenderer->assignVar('database', array('qet' => OW::getDbo()->getTotalQueryExecTime(), 'ql' => $queryLog, 'qc' => count($queryLog)));

                //events
                $eventsData = OW::getEventManager()->getLog();
                $eventsDataToAssign = array('bind' => array(), 'calls' => array());

                foreach ( $eventsData['bind'] as $eventName => $listeners )
                {
                    $listenersList = array();

                    foreach ( $listeners as $priority )
                    {
                        foreach ( $priority as $listener )
                        {
                            if ( is_array($listener) )
                            {
                                if ( is_object($listener[0]) )
                                {
                                    $listener = get_class($listener[0]) . ' -> ' . $listener[1];
                                }
                                else
                                {
                                    $listener = $listener[0] . ' :: ' . $listener[1];
                                }
                            }
                            else if ( is_string($listener) )
                            {
                                
                            }
                            else
                            {
                                $listener = 'ClosureObject';
                            }

                            $listenersList[] = $listener;
                        }
                    }

                    $eventsDataToAssign['bind'][] = array('name' => $eventName, 'listeners' => $listenersList);
                }

                foreach ( $eventsData['call'] as $eventItem )
                {
                    $listenersList = array();

                    foreach ( $eventItem['listeners'] as $priority )
                    {
                        foreach ( $priority as $listener )
                        {
                            if ( is_array($listener) )
                            {
                                if ( is_object($listener[0]) )
                                {
                                    $listener = get_class($listener[0]) . ' -> ' . $listener[1];
                                }
                                else
                                {
                                    $listener = $listener[0] . ' :: ' . $listener[1];
                                }
                            }
                            else if ( is_string($listener) )
                            {
                                
                            }
                            else
                            {
                                $listener = 'ClosureObject';
                            }

                            $listenersList[] = $listener;
                        }
                    }

                    $paramsData = var_export($eventItem['event']->getParams(), true);
                    $eventsDataToAssign['call'][] = array('type' => $eventItem['type'], 'name' => $eventItem['event']->getName(), 'listeners' => $listenersList, 'params' => $paramsData, 'start' => sprintf('%.3f', $eventItem['start']), 'exec' => sprintf('%.3f', $eventItem['exec']));
                }

                $eventsDataToAssign['bindsCount'] = count($eventsDataToAssign['bind']);
                $eventsDataToAssign['callsCount'] = count($eventsDataToAssign['call']);
                $viewRenderer->assignVar('events', $eventsDataToAssign);
                //printVar($eventsDataToAssign);
                $output = $viewRenderer->renderTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'dev_tools_tpl.html');

                $viewRenderer->clearAssignedVars();
                $viewRenderer->assignVars($prevVars);


                $event->add($output);
            }
            OW::getEventManager()->bind('base.append_markup', 'base_dev_tool');
        }

        if ( !defined('OW_DEV_MODE') || !OW_DEV_MODE )
        {
            return;
        }
        else
        {
            $this->updateCachedEntities(OW_DEV_MODE);
        }

        if ( isset($_GET['clear']) && $_GET['clear'] = 'ctpl' )
        {
            OW_ViewRenderer::getInstance()->clearCompiledTpl();
        }

        if ( isset($_GET['set-theme']) )
        {
            $theme = BOL_ThemeService::getInstance()->findThemeByName(trim($_GET['theme']));

            if ( $theme !== null )
            {
                OW::getConfig()->saveConfig('base', 'selectedTheme', $theme->getName());
            }

            $this->redirect(OW::getRequest()->buildUrlQueryString(null, array('theme' => null)));
        }
    }
}
