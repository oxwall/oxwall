<?php

/**
 * Copyright (c) 2009, Skalfa LLC
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Base language class.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_core
 * @since 1.0
 */
class OW_Language
{
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
    /**
     * Singleton instance.
     *
     * @var OW_Language
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_Language
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function text( $prefix, $key, array $vars = null )
    {
        if ( empty($prefix) || empty($key) )
        {
            return $prefix . '+' . $key;
        }

        $text = null;
        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key);
        }
        catch ( Exception $e )
        {
            return $prefix . '+' . $key;
        }

        if ( $text === null )
        {
            return $prefix . '+' . $key;
        }

        if ( !empty($vars) && is_array($vars) ) {
            foreach ($vars as $key => &$value) {
                if (UTIL_Serialize::isSerializedObject($value)) {
                    $object = UTIL_Serialize::unserialize($value);
                    if (empty($object) || !($object instanceof BASE_CLASS_LanguageParams)) {
                        $value = '';
                    }

                    $value = $object->fetch();
                }
            }
        }

        $event = new OW_Event("core.get_text", array("prefix" => $prefix, "key" => $key, "vars" => $vars));
        $this->eventManager->trigger($event);

        if ( $event->getData() !== null )
        {
            return $event->getData();
        }

        $text = UTIL_String::replaceVars($text, $vars);

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
