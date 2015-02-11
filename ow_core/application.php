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
 * Description...
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_Application
{
    const CONTEXT_MOBILE = BOL_UserService::USER_CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = BOL_UserService::USER_CONTEXT_DESKTOP;
    const CONTEXT_API = BOL_UserService::USER_CONTEXT_API;
    const CONTEXT_NAME = 'owContext';

    /**
     * Current page document key.
     *
     * @var string
     */
    protected $documentKey;

    /**
     * @var string
     */
    protected $context;

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
     * @var OW_Application
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_Application
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
     * Sets site maintenance mode.
     *
     * @param boolean $mode
     */
    public function setMaintenanceMode( $mode )
    {
        OW::getConfig()->saveConfig('base', 'maintenance', (bool) $mode);
    }

    /**
     * @return string
     */
    public function getDocumentKey()
    {
        return $this->documentKey;
    }

    /**
     * @param string $key
     */
    public function setDocumentKey( $key )
    {
        $this->documentKey = $key;
    }

    /**
     * Application init actions.
     */
    public function init()
    {
        // router init - need to set current page uri and base url
        $router = OW::getRouter();
        $this->urlHostRedirect();
        OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());
        $this->userAutoLogin();

        // setting default time zone
        date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));

        OW::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
        OW::getRequestHandler()->setStaticPageAttributes('BASE_CTRL_StaticDocument');
        $uri = OW::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);

        $defaultRoute = new OW_DefaultRoute();
        //$defaultRoute->setControllerNamePrefix('CTRL');
        $router->setDefaultRoute($defaultRoute);

        OW::getPluginManager()->initPlugins();
        $event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
        OW::getEventManager()->trigger($event);

        $navService = BOL_NavigationService::getInstance();

        // try to find static document with current uri
        $document = $navService->findStaticDocument($uri);

        if ( $document !== null )
        {
            $this->documentKey = $document->getKey();
        }

        $beckend = OW::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            OW::getCacheManager()->setCacheBackend($beckend);
            OW::getCacheManager()->setLifetime(3600);
            OW::getDbo()->setUseCashe(true);
        }

        $this->devActions();

        OW::getThemeManager()->initDefaultTheme();

        // setting current theme
        $activeThemeName = OW::getEventManager()->call('base.get_active_theme_name');
        $activeThemeName = $activeThemeName ? $activeThemeName : OW::getConfig()->getValue('base', 'selectedTheme');

        if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && OW::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
        {
            OW_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(trim($activeThemeName)));
        }

        // adding static document routes
        $staticDocs = $navService->findAllStaticDocuments();
        $staticPageDispatchAttrs = OW::getRequestHandler()->getStaticPageAttributes();

        /* @var $value BOL_Document */
        foreach ( $staticDocs as $value )
        {
            OW::getRouter()->addRoute(new OW_Route($value->getKey(), $value->getUri(), $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => $value->getKey()))));

            // TODO refactor - hotfix for TOS page
            if ( UTIL_String::removeFirstAndLastSlashes($value->getUri()) == 'terms-of-use' )
            {
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.members_only', $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => $value->getKey()));
            }
        }

        //adding index page route
        $item = BOL_NavigationService::getInstance()->findFirstLocal((OW::getUser()->isAuthenticated() ? BOL_NavigationService::VISIBLE_FOR_MEMBER : BOL_NavigationService::VISIBLE_FOR_GUEST), OW_Navigation::MAIN);

        if ( $item !== null )
        {
            if ( $item->getRoutePath() )
            {
                $route = OW::getRouter()->getRoute($item->getRoutePath());
                $ddispatchAttrs = $route->getDispatchAttrs();
            }
            else
            {
                $ddispatchAttrs = OW::getRequestHandler()->getStaticPageAttributes();
            }

            $router->addRoute(new OW_Route('base_default_index', '/', $ddispatchAttrs['controller'], $ddispatchAttrs['action'], array('documentKey' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => $item->getDocumentKey()))));
            $this->indexMenuItem = $item;
            OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'activateMenuItem'));
        }
        else
        {
            $router->addRoute(new OW_Route('base_default_index', '/', 'BASE_CTRL_ComponentPanel', 'index'));
        }

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getResponse()->setDocument($this->newDocument());
            OW::getDocument()->setMasterPage(new OW_MasterPage());
            OW::getResponse()->setHeader(OW_Response::HD_CNT_TYPE, OW::getDocument()->getMime() . '; charset=' . OW::getDocument()->getCharset());
        }
        else
        {
            OW::getResponse()->setDocument(new OW_AjaxDocument());
        }

        /* additional actions */
        if ( OW::getUser()->isAuthenticated() )
        {
            BOL_UserService::getInstance()->updateActivityStamp(OW::getUser()->getId(), $this->getContext());
        }

        // adding global template vars
        $currentThemeImagesDir = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl();
        $viewRenderer = OW_ViewRenderer::getInstance();
        $viewRenderer->assignVar('themeImagesUrl', $currentThemeImagesDir);
        $viewRenderer->assignVar('siteName', OW::getConfig()->getValue('base', 'site_name'));
        $viewRenderer->assignVar('siteTagline', OW::getConfig()->getValue('base', 'site_tagline'));
        $viewRenderer->assignVar('siteUrl', OW_URL_HOME);
        $viewRenderer->assignVar('bottomPoweredByLink', '<a href="http://www.oxwall.org/" target="_blank" title="Powered by Oxwall Community Software"><img src="' . $currentThemeImagesDir . 'powered-by-oxwall.png" alt="Oxwall Community Software" /></a>');
        $viewRenderer->assignVar('adminDashboardIframeUrl', "//static.oxwall.org/spotlight/?platform=oxwall&platform-version=" . OW::getConfig()->getValue('base', 'soft_version') . "&platform-build=" . OW::getConfig()->getValue('base', 'soft_build'));

        if ( function_exists('ow_service_actions') )
        {
            call_user_func('ow_service_actions');
        }

        $this->handleHttps();
    }

    /**
     * Finds controller and action for current request.
     */
    public function route()
    {
        try
        {
            OW::getRequestHandler()->setHandlerAttributes(OW::getRouter()->route());
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
        }

        $this->httpVsHttpsRedirect();
    }

    /**
     * ---------
     */
    public function handleRequest()
    {
        $baseConfigs = OW::getConfig()->getValues('base');

        //members only
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW && !OW::getUser()->isAuthenticated() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_User',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'standardSignIn'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.members_only', $attributes);
            $this->addCatchAllRequestsException('base.members_only_exceptions', 'base.members_only');
        }

        //splash screen
        if ( (bool) OW::getConfig()->getValue('base', 'splash_screen') && !isset($_COOKIE['splashScreen']) )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'splashScreen',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_JS => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ROUTE => 'base_page_splash_screen'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.splash_screen', $attributes);
            $this->addCatchAllRequestsException('base.splash_screen_exceptions', 'base.splash_screen');
        }

        // password protected
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_PASSWORD_VIEW && !OW::getUser()->isAuthenticated() && !isset($_COOKIE['base_password_protection'])
        )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'passwordProtection'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.password_protected', $attributes);
            $this->addCatchAllRequestsException('base.password_protected_exceptions', 'base.password_protected');
        }

        // maintenance mode
        if ( (bool) $baseConfigs['maintenance'] && !OW::getUser()->isAdmin() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'maintenance',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.maintenance_mode', $attributes);
            $this->addCatchAllRequestsException('base.maintenance_mode_exceptions', 'base.maintenance_mode');
        }

        try
        {
            OW::getRequestHandler()->dispatch();
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
            $this->handleRequest();
        }
    }

    /**
     * Method called just before request responding.
     */
    public function finalize()
    {
        $document = OW::getDocument();

        $meassages = OW::getFeedback()->getFeedback();

        foreach ( $meassages as $messageType => $messageList )
        {
            foreach ( $messageList as $message )
            {
                $document->addOnloadScript("OW.message(" . json_encode($message) . ", '" . $messageType . "');");
            }
        }

        $event = new OW_Event(OW_EventManager::ON_FINALIZE);
        OW::getEventManager()->trigger($event);
    }

    /**
     * System method. Don't call it!!!
     */
    public function onBeforeDocumentRender()
    {
        $document = OW::getDocument();

        $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'ow.css' . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -100);
        $document->addStyleSheet(OW::getThemeManager()->getCssFileUrl() . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', (-90));

        // add custom css if page is not admin TODO replace with another condition
        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( OW::getThemeManager()->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
            {
                $document->addStyleSheet(OW::getThemeManager()->getThemeService()->getCustomCssFileUrl(OW::getThemeManager()->getCurrentTheme()->getDto()->getName()));
            }

            if ( $this->getDocumentKey() !== 'base.sign_in' )
            {
                $customHeadCode = OW::getConfig()->getValue('base', 'html_head_code');
                $customAppendCode = OW::getConfig()->getValue('base', 'html_prebody_code');

                if ( !empty($customHeadCode) )
                {
                    $document->addCustomHeadInfo($customHeadCode);
                }

                if ( !empty($customAppendCode) )
                {
                    $document->appendBody($customAppendCode);
                }
            }
        }
        else
        {
            $document->addStyleSheet(OW::getPluginManager()->getPlugin('admin')->getStaticCssUrl() . 'admin.css' . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -50);
        }

        $language = OW::getLanguage();

        if ( $document->getTitle() === null )
        {
            $document->setTitle($language->text('nav', 'page_default_title'));
        }

        if ( $document->getDescription() === null )
        {
            $document->setDescription($language->text('nav', 'page_default_description'));
        }

        /* if ( $document->getKeywords() === null )
          {
          $document->setKeywords($language->text('nav', 'page_default_keywords'));
          } */

        if ( $document->getHeadingIconClass() === null )
        {
            $document->setHeadingIconClass('ow_ic_file');
        }

        if ( !empty($this->documentKey) )
        {
            $document->setBodyClass($this->documentKey);
        }

        if ( $this->getDocumentKey() !== null )
        {
            $masterPagePath = OW::getThemeManager()->getDocumentMasterPage($this->getDocumentKey());

            if ( $masterPagePath !== null )
            {
                $document->getMasterPage()->setTemplate($masterPagePath);
            }
        }
    }

    /**
     * Triggers response object to send rendered page.
     */
    public function returnResponse()
    {
        OW::getResponse()->respond();
    }

    /**
     * Makes header redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null, $switchContextTo = false )
    {
        if ( $switchContextTo !== false && in_array($switchContextTo, array(self::CONTEXT_DESKTOP, self::CONTEXT_MOBILE)) )
        {
            OW::getSession()->set(self::CONTEXT_NAME, $switchContextTo);
        }

        // if empty redirect location -> current URI is used
        if ( $redirectTo === null )
        {
            $redirectTo = OW::getRequest()->getRequestUri();
        }

        // if URI is provided need to add site home URL
        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
        {
            $redirectTo = OW::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
        }

        UTIL_Url::redirect($redirectTo);
    }
    /**
     * Menu item to activate.
     *
     * @var BOL_MenuItem
     */
    private $indexMenuItem;

    public function activateMenuItem()
    {
        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( OW::getRequest()->getRequestUri() === '/' || OW::getRequest()->getRequestUri() === '' )
            {
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $this->indexMenuItem->getPrefix(), $this->indexMenuItem->getKey());
            }
        }
    }
    /* private auxilary methods */

    protected function newDocument()
    {
        $language = BOL_LanguageService::getInstance()->getCurrent();
        $document = new OW_HtmlDocument();
        $document->setCharset('UTF-8');
        $document->setMime('text/html');
        $document->setLanguage($language->getTag());

        if ( $language->getRtl() )
        {
            $document->setDirection('rtl');
        }
        else
        {
            $document->setDirection('ltr');
        }

        if ( (bool) OW::getConfig()->getValue('base', 'favicon') )
        {
            $document->setFavicon(OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico');
        }

        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));

        //$document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'json2.js', 'text/javascript', (-99));
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'text/javascript', (-50));

        $onloadJs = "OW.bindAutoClicks();OW.bindTips($('body'));";

        if ( OW::getUser()->isAuthenticated() )
        {
            $activityUrl = OW::getRouter()->urlFor('BASE_CTRL_User', 'updateActivity');
            $onloadJs .= "OW.getPing().addCommand('user_activity_update').start(600000);";
        }

        $document->addOnloadScript($onloadJs);
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));

        return $document;
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

    protected function updateCachedEntities( $options )
    {
        $options = intval($options);

        if ( $options === 1 || $options & 1 << 1 )
        {
            OW_ViewRenderer::getInstance()->clearCompiledTpl();
        }

        if ( $options === 1 || $options & 1 << 2 )
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

        if ( $options === 1 || $options & 1 << 3 )
        {
            BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
        }

        if ( $options === 1 || $options & 1 << 4 )
        {
            OW::getCacheManager()->clean(array(), OW_CacheManager::CLEAN_ALL);
        }

        if ( ( $options === 1 || $options & 1 << 5 ) && !defined('OW_PLUGIN_XP') )
        {
            $pluginService = BOL_PluginService::getInstance();
            $activePlugins = $pluginService->findActivePlugins();

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
    }

    protected function urlHostRedirect()
    {
        $urlArray = parse_url(OW_URL_HOME);
        $constHost = $urlArray['host'];

        if ( isset($_SERVER['HTTP_HOST']) && ( $_SERVER['HTTP_HOST'] !== $constHost ) )
        {
            $this->redirect(OW_URL_HOME . OW::getRequest()->getRequestUri());
        }
    }
    /**
     * @var array 
     */
    protected $httpsHandlerAttrsList = array();

    public function addHttpsHandlerAttrs( $controller, $action = false )
    {
        $this->httpsHandlerAttrsList[] = array(OW_RequestHandler::ATTRS_KEY_CTRL => $controller, OW_RequestHandler::ATTRS_KEY_ACTION => $action);
    }

    protected function httpVsHttpsRedirect()
    {
        $isSsl = OW::getRequest()->isSsl();

        if ( $isSsl === null )
        {
            return;
        }

        $attrs = OW::getRequestHandler()->getHandlerAttributes();
        $specAttrs = false;

        foreach ( $this->httpsHandlerAttrsList as $item )
        {
            if ( $item[OW_RequestHandler::ATTRS_KEY_CTRL] == $attrs[OW_RequestHandler::ATTRS_KEY_CTRL] && ( empty($item[OW_RequestHandler::ATTRS_KEY_ACTION]) || $item[OW_RequestHandler::ATTRS_KEY_ACTION] == $attrs[OW_RequestHandler::ATTRS_KEY_ACTION] ) )
            {
                $specAttrs = true;
                if ( !$isSsl )
                {
                    $this->redirect(str_replace("http://", "https://", OW_URL_HOME) . OW::getRequest()->getRequestUri());
                }
            }
        }

        if ( $specAttrs )
        {
            return;
        }

        $urlArray = parse_url(OW_URL_HOME);

        if ( !empty($urlArray["scheme"]) )
        {
            $homeUrlSsl = ($urlArray["scheme"] == "https");

            if ( ($isSsl && !$homeUrlSsl) || (!$isSsl && $homeUrlSsl) )
            {
                $this->redirect(OW_URL_HOME . OW::getRequest()->getRequestUri());
            }
        }
    }

    protected function handleHttps()
    {
        if ( !OW::getRequest()->isSsl() )
        {
            return;
        }

        function base_post_handle_https_static_content()
        {
            $markup = OW::getResponse()->getMarkup();
            $matches = array();
            preg_match_all("/<a([^>]+?)>(.+?)<\/a>/", $markup, $matches);
            $search = array_unique($matches[0]);
            $replace = array();
            $contentReplaceArr = array();

            for ( $i = 0; $i < sizeof($search); $i++ )
            {
                $replace[] = "<#|#|#" . $i . "#|#|#>";
                if ( mb_strstr($matches[2][$i], "http:") )
                {
                    $contentReplaceArr[] = $i;
                }
            }

            $markup = str_replace($search, $replace, $markup);
            $markup = str_replace("http:", "https:", $markup);

            foreach ( $contentReplaceArr as $index )
            {
                $search[$index] = str_replace($matches[2][$index], str_replace("http:", "https:", $matches[2][$index]), $search[$index]);
            }

            $markup = str_replace($replace, $search, $markup);

            OW::getResponse()->setMarkup($markup);
        }
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_DOCUMENT_RENDER, "base_post_handle_https_static_content");
    }

    protected function userAutoLogin()
    {
        if ( OW::getSession()->isKeySet('no_autologin') )
        {
            OW::getSession()->delete('no_autologin');
            return;
        }

        if ( !empty($_COOKIE['ow_login']) && !OW::getUser()->isAuthenticated() )
        {
            $id = BOL_UserService::getInstance()->findUserIdByCookie(trim($_COOKIE['ow_login']));

            if ( !empty($id) )
            {
                OW_User::getInstance()->login($id);
                $loginCookie = BOL_UserService::getInstance()->findLoginCookieByUserId($id);
                setcookie('ow_login', $loginCookie->getCookie(), (time() + 86400 * 7), '/', null, false, true);
            }
        }
    }

    protected function addCatchAllRequestsException( $eventName, $key )
    {
        $event = new BASE_CLASS_EventCollector($eventName);
        OW::getEventManager()->trigger($event);
        $exceptions = $event->getData();

        foreach ( $exceptions as $item )
        {
            if ( is_array($item) && !empty($item['controller']) && !empty($item['action']) )
            {
                OW::getRequestHandler()->addCatchAllRequestsExclude($key, trim($item['controller']), trim($item['action']));
            }
        }
    }

    public function getContext()
    {
        return $this->context;
    }

    public function isMobile()
    {
        return $this->context == self::CONTEXT_MOBILE;
    }

    public function isDesktop()
    {
        return $this->context == self::CONTEXT_DESKTOP;
    }

    public function isApi()
    {
        return $this->context == self::CONTEXT_API;
    }
}
