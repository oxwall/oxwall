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
class OW_MobileApplication extends OW_Application
{

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->context = self::CONTEXT_MOBILE;
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
     * @return OW_MobileApplication
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
     * Application init actions.
     */
    public function init()
    {
        // router init - need to set current page uri and base url
        $router = OW::getRouter();
        $router->setBaseUrl(OW_URL_HOME);

        $this->urlHostRedirect();

        OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

        $this->userAutoLogin();

        // setting default time zone
        date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));

//        OW::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
        OW::getRequestHandler()->setStaticPageAttributes('BASE_MCTRL_BaseDocument', 'staticDocument');

        $uri = OW::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);
        $defaultRoute = new OW_DefaultRoute();
        //$defaultRoute->setControllerNamePrefix('MCTRL');
        $router->setDefaultRoute($defaultRoute);

        $navService = BOL_NavigationService::getInstance();
//
//        // try to find static document with current uri
//        $document = $navService->findStaticDocument($uri);
//
//        if ( $document !== null )
//        {
//            $this->documentKey = $document->getKey();
//        }

        OW::getPluginManager()->initPlugins();
        $event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
        OW::getEventManager()->trigger($event);

        $beckend = OW::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            OW::getCacheManager()->setCacheBackend($beckend);
            OW::getCacheManager()->setLifetime(3600);
            OW::getDbo()->setUseCashe(true);
        }

        $this->devActions();

        OW::getThemeManager()->initDefaultTheme(true);

        // setting current theme
        $activeThemeName = OW::getEventManager()->call('base.get_active_theme_name');
        $activeThemeName = $activeThemeName ? $activeThemeName : OW::getConfig()->getValue('base', 'selectedTheme');

        if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && OW::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
        {
            OW_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(trim($activeThemeName), true));
        }

        // adding static document routes
        $staticDocs = $navService->findAllMobileStaticDocuments();
        $staticPageDispatchAttrs = OW::getRequestHandler()->getStaticPageAttributes();

        /* @var $value BOL_Document */
        foreach ( $staticDocs as $value )
        {
            OW::getRouter()->addRoute(new OW_Route($value->getKey(), $value->getUri(), $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => $value->getKey()))));

            // TODO refactor - hotfix for TOS page
//            if ( UTIL_String::removeFirstAndLastSlashes($value->getUri()) == 'terms-of-use' )
//            {
//                OW::getRequestHandler()->addCatchAllRequestsExclude('base.members_only', $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => $value->getKey()));
//            }
        }

        //adding index page route
        $item = BOL_NavigationService::getInstance()->findFirstLocal((OW::getUser()->isAuthenticated() ? BOL_NavigationService::VISIBLE_FOR_MEMBER : BOL_NavigationService::VISIBLE_FOR_GUEST), OW_Navigation::MOBILE_TOP);

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
            $router->addRoute(new OW_Route('base_default_index', '/', 'BASE_MCTRL_WidgetPanel', 'index'));
        }

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getResponse()->setDocument($this->newDocument());
            OW::getDocument()->setMasterPage(new OW_MobileMasterPage());
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
        $viewRenderer->assignVar('isAuthenticated', OW::getUser()->isAuthenticated());
        $viewRenderer->assignVar('bottomPoweredByLink', '<a href="http://www.oxwall.org/" target="_blank" title="Powered by Oxwall Community Software"><img src="' . $currentThemeImagesDir . 'powered-by-oxwall.png" alt="Oxwall Community Software" /></a>');
        $viewRenderer->assignVar('adminDashboardIframeUrl', "http://static.oxwall.org/spotlight/?platform=oxwall&platform-version=" . OW::getConfig()->getValue('base', 'soft_version') . "&platform-build=" . OW::getConfig()->getValue('base', 'soft_build'));

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
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_User',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'standardSignIn'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.members_only', $attributes);
            $this->addCatchAllRequestsException('base.members_only_exceptions', 'base.members_only');
        }

        //splash screen
        if ( (bool) OW::getConfig()->getValue('base', 'splash_screen') && !isset($_COOKIE['splashScreen']) )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_BaseDocument',
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
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'passwordProtection'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.password_protected', $attributes);
            $this->addCatchAllRequestsException('base.password_protected_exceptions', 'base.password_protected');
        }

        // maintenance mode
        if ( (bool) $baseConfigs['maintenance'] && !OW::getUser()->isAdmin() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_BaseDocument',
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
                $document->addOnloadScript("OWM.message(" . json_encode($message) . ", '" . $messageType . "');");
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

        $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'mobile.css' . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -100);
        $document->addStyleSheet(OW::getThemeManager()->getCssFileUrl(true) . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', (-90));

        if ( OW::getThemeManager()->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
        {
            $document->addStyleSheet(OW::getThemeManager()->getThemeService()->getCustomCssFileUrl(OW::getThemeManager()->getCurrentTheme()->getDto()->getName(), true));
        }

        $language = OW::getLanguage();

        if ( $document->getTitle() === null )
        {
            $document->setTitle($language->text('mobile', 'page_default_title'));
        }

        if ( $document->getDescription() === null )
        {
            $document->setDescription($language->text('mobile', 'page_default_description'));
        }

        if ( $document->getHeading() === null )
        {
            $document->setHeading($language->text('mobile', 'page_default_heading'));
        }

        $document->addMetaInfo('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
    }

    public function activateMenuItem()
    {
        
    }

    protected function newDocument()
    {
        $language = BOL_LanguageService::getInstance()->getCurrent();
        $document = new OW_HtmlDocument();
        $document->setTemplate(OW::getThemeManager()->getMasterPageTemplate('mobile_html_document'));
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
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile.js?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'text/javascript', (-50));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));

        return $document;
    }
}
