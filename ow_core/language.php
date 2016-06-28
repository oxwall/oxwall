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
 * Base language class.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_core
 * @method static OW_Language getInstance()
 * @since 1.0
 */
class OW_Language
{
    use OW_Singleton;
    
    /**
     * @var OW_EventManager
     */
    private $eventManager;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->eventManager = OW::getEventManager();
    }    

    public function text( $prefix, $key, array $vars = null, $defaultValue = null )
    {
        if ( empty($prefix) || empty($key) )
        {
            return $prefix . '+' . $key;
        }

        $text = null;
        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key, $vars);
        }
        catch ( Exception $e )
        {
            return $defaultValue === null ? $prefix . '+' . $key : $defaultValue;
        }

        if ( $text === null )
        {
            return $defaultValue === null ? $prefix . '+' . $key : $defaultValue;
        }

        return $text;
    }

    public function valueExist( $prefix, $key )
    {
        if ( empty($prefix) || empty($key) )
        {
            throw new InvalidArgumentException('Invalid parameter $prefix or $key');
        }

        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key);
        }
        catch ( Exception $e )
        {
            return false;
        }

        if ( $text === null )
        {
            return false;
        }

        return true;
    }

    public function addKeyForJs( $prefix, $key )
    {
        $text = json_encode($this->text($prefix, $key));

        OW::getDocument()->addOnloadScript("OW.registerLanguageKey('$prefix', '$key', $text);", -99);
    }

    public function getCurrentId()
    {
        return BOL_LanguageService::getInstance()->getCurrent()->getId();
    }

    public function importPluginLangs( $path, $key, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromZip($path, $key, $refreshCache, $addLanguage);
    }
    
    public function importLangsFromZip( $path, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromZip($path, uniqid(), $refreshCache, $addLanguage);
    }

    public function importLangsFromDir( $path, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromDir($path, $refreshCache, $addLanguage);
    }
}
