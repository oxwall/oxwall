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
if ( !defined("OW_DIR_USERFILES") )
{
    define("OW_DIR_USERFILES", OW_DIR_ROOT . "ow_userfiles" . DS);
}

if ( !defined("OW_DIR_STATIC") )
{
    define("OW_DIR_STATIC", OW_DIR_ROOT . "ow_static" . DS);
}
if ( !defined("OW_URL_STATIC") )
{
    define("OW_URL_STATIC", OW_URL_HOME . "ow_static/");
}

if ( !defined("OW_URL_USERFILES") )
{
    define("OW_URL_USERFILES", OW_URL_HOME . "ow_userfiles/");
}

if ( !defined("OW_DIR_PLUGINFILES") )
{
    define("OW_DIR_PLUGINFILES", OW_DIR_ROOT . "ow_pluginfiles/");
}

if ( !defined("OW_DIR_CORE") )
{
    define("OW_DIR_CORE", OW_DIR_ROOT . "ow_core" . DS);
}

if ( !defined("OW_DIR_INC") )
{
    define("OW_DIR_INC", OW_DIR_ROOT . "ow_includes" . DS);
}

if ( !defined("OW_DIR_LIB") )
{
    define("OW_DIR_LIB", OW_DIR_ROOT . "ow_libraries" . DS);
}

if ( !defined("OW_DIR_UTIL") )
{
    define("OW_DIR_UTIL", OW_DIR_ROOT . "ow_utilities" . DS);
}

if ( !defined("OW_DIR_PLUGIN") )
{
    define("OW_DIR_PLUGIN", OW_DIR_ROOT . "ow_plugins" . DS);
}

if ( !defined("OW_DIR_THEME") )
{
    define("OW_DIR_THEME", OW_DIR_ROOT . "ow_themes" . DS);
}

if ( !defined("OW_DIR_SYSTEM_PLUGIN") )
{
    define("OW_DIR_SYSTEM_PLUGIN", OW_DIR_ROOT . "ow_system_plugins" . DS);
}

if ( !defined("OW_DIR_SMARTY") )
{
    define("OW_DIR_SMARTY", OW_DIR_ROOT . "ow_smarty" . DS);
}

define("OW_DIR_STATIC_PLUGIN", OW_DIR_STATIC . "plugins" . DS);
define("OW_DIR_STATIC_THEME", OW_DIR_STATIC . "themes" . DS);
define("OW_DIR_PLUGIN_USERFILES", OW_DIR_USERFILES . "plugins" . DS);
define("OW_DIR_THEME_USERFILES", OW_DIR_USERFILES . "themes" . DS);
define("OW_DIR_LOG", OW_DIR_ROOT . "ow_log" . DS);

if ( defined("OW_URL_STATIC") )
{
    define("OW_URL_STATIC_THEMES", OW_URL_STATIC . "themes/");
    define("OW_URL_STATIC_PLUGINS", OW_URL_STATIC . "plugins/");
}

if ( defined("OW_URL_USERFILES") )
{
    define("OW_URL_PLUGIN_USERFILES", OW_URL_USERFILES . "plugins/");
    define("OW_URL_THEME_USERFILES", OW_URL_USERFILES . "themes/");
}

