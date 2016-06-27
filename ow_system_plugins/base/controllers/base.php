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
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Base extends OW_ActionController
{
    public function index()
    {
        //TODO implement
    }
    
    public function turnDevModeOn()
    {
        if( OW_DEV_MODE || OW_PROFILER_ENABLE )
        {
            OW::getConfig()->saveConfig('base', 'dev_mode', 1);
        }
        
        if( !empty($_GET['back-uri']) )
        {
            $this->redirect(urldecode($_GET['back-uri']));
        }
        else
        {
            $this->redirect(OW_URL_HOME);
        }
    }

    public function robotsTxt()
    {
        if( file_exists(OW_DIR_ROOT.'robots.txt') )
        {
            header("Content-Type: text/plain");
            echo(file_get_contents(OW_DIR_ROOT.'robots.txt'));
            exit;
        }

        throw new Redirect404Exception();
    }

    /**
     * Sitemap
     */
    public function sitemap()
    {
        $part = isset($_GET['part']) ? (int) $_GET['part'] : null;

        $sitemap = BOL_SeoService::getInstance()->getSitemapPath($part);

        if ( file_exists($sitemap) )
        {
            header('Content-Type: text/xml');

            echo file_get_contents($sitemap);
            exit;
        }

        throw new Redirect404Exception();
    }
}
