<?php

define('_OW_', true);
define('DS', DIRECTORY_SEPARATOR);
define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('OW_CRON', true);

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

OW::getRouter()->setBaseUrl(OW_URL_HOME);

date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));
OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

OW::getPluginManager()->initPlugins();
$event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
OW::getEventManager()->trigger($event);

OW::getThemeManager()->initDefaultTheme();

// setting current theme
$activeThemeName = OW::getConfig()->getValue('base', 'selectedTheme');

if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && OW::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
{
    OW_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByKey(trim($activeThemeName)));
}