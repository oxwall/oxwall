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
class BASE_Import extends DATAIMPORTER_CLASS_Import
{
    public $configs;

    public function import( $params )
    {
        $importDir = $params['importDir'];

        $sqlFile = $importDir . 'configs.sql';

        //import configs
        if ( file_exists($sqlFile) )
        {
            DATAIMPORTER_BOL_ImportService::getInstance()->sqlImport($sqlFile);
        }

        $configFile = $importDir . 'config.txt';

        $string = file_get_contents($configFile);
        $this->configs = json_decode($string, true);

        $this->importAvatars($this->configs['avatarUrl']);

        $this->importTheme($importDir);

        $this->importMediaPanelFiles();

        if ( OW::getPluginManager()->getPlugin('dataimporter') )
        {
            if ( file_exists(OW::getPluginManager()->getPlugin('dataimporter')->getRootDir() . 'langs.zip') )
            {
                OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('dataimporter')->getRootDir() . 'langs.zip', 'dataimporter');
            }
        }

        $languageId = OW::getLanguage()->getCurrentId();
        BOL_LanguageService::getInstance()->generateCache($languageId);

        OW::getDbo()->query( "TRUNCATE " . OW_DB_PREFIX . 'base_component_place_cache' ); //TODO: Use service function

        // ADD MENU ITEMS add plugin and add theme
        try
        {
            OW::getDbo()->query( "INSERT IGNORE INTO `" . OW_DB_PREFIX ."base_menu_item` ( `prefix`, `key`, `documentKey`, `type`, `order`, `routePath`, `externalUrl`, `newWindow`, `visibleFor`) VALUES ( 'admin', 'sidebar_menu_plugins_add', '', 'admin_plugins', 3, 'admin_plugins_add', NULL, 0, 2) ");
        }
        catch( Exception $ex )
        {
            
        }

        try
        {
            OW::getDbo()->query( "INSERT IGNORE INTO `" . OW_DB_PREFIX ."base_menu_item` ( `prefix`, `key`, `documentKey`, `type`, `order`, `routePath`, `externalUrl`, `newWindow`, `visibleFor`) VALUES ( 'admin', 'sidebar_menu_themes_add', '', 'admin_appearance', 3, 'admin_themes_add_new', NULL, 0, 3) ");
        }
        catch( Exception $ex )
        {

        }
    }

    private function importAvatars( $avatarUrl )
    {
       $avatarUrl = trim($avatarUrl);

        if ( substr($avatarUrl, -1) === '/' )
        {
            $avatarUrl = substr($avatarUrl, 0, -1);
        }

        $avatarDir = BOL_AvatarService::getInstance()->getAvatarsDir();

        $first = 0;
        $count = 150;

        while ( true )
        {
            $list = BOL_UserService::getInstance()->findList($first, $count, true);

            $first += $count;
            if ( empty($list) )
            {
                break;
            }

            foreach ( $list as $user )
            {
                for ( $size = 1; $size < 4; $size++ )
                {
                    $path = BOL_AvatarService::getInstance()->getAvatarPath($user->id, $size);
                    $avatarName = str_replace($avatarDir, '', $path);
                    $content = file_get_contents($avatarUrl . '/' . $avatarName);

                    if ( !empty($content) )
                    {
                        OW::getStorage()->fileSetContent($path, $content);
                    }
                }
            }
        }
    }

    private function importTheme( $importDir )
    {
        $theme = new BOL_Theme();

        $theme->name = $this->configs['currentTheme']['name'];
        $theme->customCss = $this->configs['currentTheme']['customCss'];
        $theme->customCssFileName = $this->configs['currentTheme']['customCssFileName'];
        $theme->description = $this->configs['currentTheme']['description'];
        $theme->sidebarPosition = $this->configs['currentTheme']['sidebarPosition'];
        $theme->title = $this->configs['currentTheme']['title'];

        if ( !defined('OW_PLUGIN_XP') )
        {
            OW::getStorage()->copyDir($importDir . ($theme->name) . DS, BOL_ThemeService::getInstance()->getRootDir($theme->name));
        }

        OW::getStorage()->copyDir($importDir . 'themes' . DS, OW_DIR_THEME_USERFILES);

        BOL_ThemeService::getInstance()->processAllThemes();
        
        $oldTheme = BOL_ThemeService::getInstance()->findThemeByKey($theme->name);        
        $theme->id = $oldTheme->id;
        
        BOL_ThemeService::getInstance()->saveTheme($theme);

        $controlValues = array();
        $url_pattern = '/http:\/\/[^\s]+\/([\w\.]+)/i';
        
        foreach ( $this->configs['controlValue'] as $key => $controlValue )
        {
            $value = $controlValue;
            
            if ( preg_match($url_pattern, $controlValue, $matches) )
            {
                $imgFile = BOL_ThemeService::getInstance()->getUserfileImagesDir() . $matches[1];
                
                if ( !empty($matches[1]) && file_exists($imgFile) && is_file($imgFile) )
                {
                    $value = 'url(' . BOL_ThemeService::getInstance()->getUserfileImagesUrl() . $matches[1] . ')';
                }
            }

            $controlValues[$key] = $value;
        }
        
        BOL_ThemeService::getInstance()->importThemeControls($theme->id, $controlValues);
        BOL_ThemeService::getInstance()->processAllThemes();
    }
    
    private function importMediaPanelFiles()
    {
        $mediaPanelUrl = $this->configs['media_panel_url'];

        $list = array();

        $list = BOL_MediaPanelService::getInstance()->findAll();
        
        $list = is_array($list)?  $list: array();
        
        foreach ($list as $dto)/*@var $dto BOL_MediaPanelFile*/
        {
            $filename = $dto->getId().'-'.$dto->getData()->name;
            
            $fileContent = file_get_contents($mediaPanelUrl.'/'.$filename);

            if ( !empty($fileContent) )
            {
                OW::getStorage()->fileSetContent(OW::getPluginManager()->getPlugin('base')->getUserFilesDir().$filename, $fileContent);
            }
        }
    }
}
