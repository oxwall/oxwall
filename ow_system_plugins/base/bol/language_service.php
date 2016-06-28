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
 * Singleton. Language Service
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageService
{
    const LANG_ID_VAR_NAME = "base_language_id";

    /**
     * @var BOL_Language
     */
    protected $currentLanguage;

    /**
     * @var int
     */
    protected $currentLanguageId;

    /**
     * @var array
     */
    protected $language = array();

    /**
     * @var BOL_LanguageDao
     */
    protected $languageDao;

    /**
     * @var BOL_LanguagePrefixDao
     */
    protected $prefixDao;

    /**
     * @var BOL_LanguageKeyDao
     */
    protected $keyDao;

    /**
     * @var BOL_LanguageValueDao
     */
    protected $valueDao;

    /**
     * @var array
     */
    private $exceptionPrefixes = array( "mobile", "nav", "ow_custom" ); // section which importing without checking plugin key

    /**
     * Class instance
     *
     * @var BOL_LanguageService
     */
    private static $classInstance;

    /**
     * Returns class instance
     * @param $includeCache bool
     * @return BOL_LanguageService
     */
    public static function getInstance( $includeCache = true )
    {
        if ( !isset(self::$classInstance) )
        {
            try
            {
                self::$classInstance = OW::getClassInstance(self::class, $includeCache);
            }
            catch ( ReflectionException $ex )
            {
                self::$classInstance = new self($includeCache);
            }
        }

        return self::$classInstance;
    }

    /**
     * BOL_LanguageService constructor.
     * @param bool $includeCache
     */
    private function __construct( $includeCache = true )
    {
        $this->languageDao = BOL_LanguageDao::getInstance();
        $this->prefixDao = BOL_LanguagePrefixDao::getInstance();
        $this->keyDao = BOL_LanguageKeyDao::getInstance();
        $this->valueDao = BOL_LanguageValueDao::getInstance();

        if ( $includeCache )
        {
            $this->loadFromCache();
        }
    }

    /**
     * Generates cache for provided language
     *
     * @param int $languageId
     */
    public function generateCache( $languageId )
    {
        $event = new BASE_CLASS_EventCollector('base.add_global_lang_keys');
        OW::getEventManager()->trigger($event);
        $globalVars = call_user_func_array('array_merge', $event->getData());

        $values = $this->keyDao->findAllWithValues($languageId);

        $result = array();

        foreach ( $values as $v )
        {
            $key = $v['prefix'] . '+' . $v['key'];
            $v['value'] = UTIL_String::replaceVars($v['value'], $globalVars);
            $result[$key] = $v['value'];
        }

        $cacheContent = "<?php\n\$language[{$languageId}] = " . var_export($result, true) . ";\n?>";
        $filename = $this->getLanguageCacheDir() . 'lang_' . $languageId . '.php';

        file_put_contents($filename, $cacheContent);
        @chmod($filename, 0666);

        $this->loadFromCache();
    }

    /**
     * Generates cache for all languages
     */
    public function generateCacheForAllActiveLanguages()
    {
        $languages = $this->findActiveList();
        foreach ( $languages as $lang )
        {
            /* @var $lang BOL_Language */
            $this->generateCache($lang->id);
        }
    }

    /**
     * Adds new key and value
     *
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @param string $value
     *
     * @param bool $generateCache
     * @return BOL_LanguageValue
     */
    public function addValue( $languageId, $prefix, $key, $value, $generateCache = true )
    {
        $prefixDto = $this->prefixDao->findByPrefix($prefix);

        if( !$prefixDto )
        {
            throw new LogicException("Prefix `$prefix` not found!");
        }

        if ( null === ( $keyDto = $this->findKey($prefix, $key) ) )
        {
            $keyDto = new BOL_LanguageKey();

            $keyDto->setPrefixId($prefixDto->id);
            $keyDto->setKey($key);
            $this->keyDao->save($keyDto);
        }

        $valueDto = new BOL_LanguageValue();

        $valueDto->setLanguageId($languageId)
            ->setKeyId($keyDto->getId())
            ->setValue($value);

        $this->valueDao->save($valueDto);

        if ( $generateCache )
        {
            $this->generateCache($valueDto->languageId);
        }

        return $valueDto;
    }

    /**
     * Adds new Prefix
     *
     * @param string $prefix
     * @param string $label
     * @return BOL_LanguagePrefix
     * @throws Exception
     */
    public function addPrefix( $prefix, $label )
    {
        if ( $this->prefixExists($prefix) )
        {
            throw new Exception('Duplicated prefix..');
        }

        $prefixDto = new BOL_LanguagePrefix();

        $prefixDto->setPrefix($prefix)
            ->setLabel($label);

        $this->prefixDao->save($prefixDto);

        return $prefixDto;
    }

    /**
     * @param int $first
     * @param int $count
     * @param string $prefix
     * @return array
     * @throws Exception
     */
    public function findLastKeyList( $first, $count, $prefix = null )
    {
        return $this->valueDao->findLastKeyList($first, $count, $prefix);
    }

    /**
     * @param string $prefix
     * @return int
     */
    public function countKeyByPrefix( $prefix )
    {
        $prefixId = $this->findPrefixId($prefix);

        return $this->keyDao->countKeyByPrefix($prefixId);
    }

    /**
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @return string
     */
    public function getTextTemplate( $languageId, $prefix, $key )
    {
        OW::getEventManager()->trigger( new OW_Event('servicelangtools.lang_used_log', array( 'prefix' => $prefix, 'key' => $key)) );

        return ( isset($this->language[$languageId][$prefix . '+' . $key]) ) ? $this->language[$languageId][$prefix . '+' . $key] : null;
    }

    /**
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @param array $vars
     * @return string
     */
    public function getText( $languageId, $prefix, $key, $vars = array() )
    {
        $text = $this->getTextTemplate($languageId, $prefix, $key);

        if ( !empty($vars) && is_array($vars) ) 
        {
            foreach ( $vars as $key => &$value ) 
            {
                if ( UTIL_Serialize::isSerializedObject($value) ) 
                {
                    $object = UTIL_Serialize::unserialize($value);
                    if ( empty($object) || !($object instanceof BASE_CLASS_LanguageParams) ) 
                    {
                        $value = '';
                    }

                    $value = $object->fetch();
                }
            }
        }

        $event = new OW_Event("core.get_text", array("prefix" => $prefix, "key" => $key, "vars" => $vars));
        OW::getEventManager()->trigger($event);

        if ( $event->getData() !== null )
        {
            return $event->getData();
        }

        $text = UTIL_String::replaceVars($text, $vars);

        return $text;
    }

    /**
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @return BOL_LanguageValue
     */
    public function getValue( $languageId, $prefix, $key )
    {
        $prefixDto = $this->findPrefix($prefix);

        if ( empty($prefixDto) )
        {
            return null;
        }

        $keyId = $this->keyDao->findKeyId($prefixDto->getId(), $key);

        if ( empty($keyId) )
        {
            return null;
        }

        return $this->valueDao->findValue($languageId, $keyId);
    }

    /**
     * @return array<BOL_LanguagePrefix>
     */
    public function getPrefixList()
    {
        return $this->prefixDao->findAll();
    }

    /**
     * @return array<BOL_Language>
     */
    public function getLanguages()
    {
        return $this->languageDao->findAll();
    }

    /**
     * @param int $languageId
     * @param int $first
     * @param int $count
     * @param string $search
     * @return array
     */
    public function findSearchResultKeyList( $languageId, $first, $count, $search )
    {
        return $this->valueDao->findSearchResultKeyList($languageId, $first, $count, $search);
    }

    /**
     * @param int $languageId
     * @param string $search
     * @return int mixed
     */
    public function countSearchResultKeys( $languageId, $search )
    {
        return $this->valueDao->countSearchResultKeys($languageId, $search);
    }

    /**
     * @param int $languageId
     * @param int $first
     * @param int $count
     * @param string $search
     * @return array
     */
    public function findKeySearchResultKeyList( $languageId, $first, $count, $search )
    {
        return $this->valueDao->findKeySearchResultKeyList($languageId, $first, $count, $search);
    }

    /**
     * @param int $languageId
     * @param string $search
     * @return int
     */
    public function countKeySearchResultKeys( $languageId, $search )
    {
        return $this->valueDao->countKeySearchResultKeys($languageId, $search);
    }

    /**
     * @param string $prefix
     * @param string $key
     * @param string $value
     * @param bool $generateCache
     * @param string $lang
     */
    public function replaceLangValue($prefix, $key, $value, $generateCache = false, $lang = 'en')
    {
        $defaultLanguage = $this->findByTag($lang);
        $languageId = $defaultLanguage->getId();

        if ( !$languageId )
        {
            throw new InvalidArgumentException('Invalid language tag: ' . $lang);
        }

        $keyDto = $this->findKey($prefix, $key);

        if ( !empty($keyDto) )
        {
            $valueDto = $this->findValue($languageId, $keyDto->id);

            if ( !empty($valueDto) )
            {
                $this->deleteValue($valueDto, $generateCache);
            }
        }

        $this->addOrUpdateValue($languageId, $prefix, $key, $value, $generateCache);
    }

    /**
     * Add new lang key
     *
     * @param int $prefixId
     * @param string $key
     * @return BOL_LanguageKey
     */
    public function addKey( $prefixId, $key )
    {
        $dto = new BOL_LanguageKey();
        $dto->setKey($key);
        $dto->setPrefixId($prefixId);

        $this->keyDao->save($dto);

        return $dto;
    }

    /**
     * @param string $prefix
     * @return int
     */
    public function findPrefixId( $prefix )
    {
        return $this->prefixDao->findPrefixId($prefix);
    }

    /**
     * @param int $keyId
     * @param bool $refreshCache
     */
    public function deleteKey( $keyId, $refreshCache = false )
    {
        if ( empty($keyId) )
        {
            throw new InvalidArgumentException("Empty key id passed");
        }

        $this->valueDao->deleteByKeyId($keyId);

        $this->keyDao->deleteById($keyId);

        if ( $refreshCache )
        {
            $this->generateCacheForAllActiveLanguages();
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public function getLanguageXML( $id )
    {
        $dto = $this->findById($id);

        $xml = new DOMDocument('1.0', 'utf-8');

        /* @var $rootElement DomElement */
        $rootElement = $xml->createElement('language');

        $rootElement->setAttribute('tag', $dto->getTag());
        $rootElement->setAttribute('label', $dto->getLabel());
        $rootElement->setAttribute('rtl', $dto->getRtl());

        $xml->appendChild($rootElement);

        return $xml->saveXML();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @return string
     * @throws Exception
     */
    public function getPrefixXML( $id, $languageId )
    {
        /* @var $prefix BOL_LanguagePrefix */
        if ( ($prefix = $this->prefixDao->findById($id)) == null )
        {
            throw new Exception("Prefix with id: {$id}, doesn't exist");
        }

        $xml = new DOMDocument('1.0', 'utf-8');

        $language = $this->findById($languageId);

        /* @var $rootElement DomElement */
        $rootElement = $xml->createElement('prefix');
        $rootElement->setAttribute('name', $prefix->getPrefix());
        $rootElement->setAttribute('label', $prefix->getLabel());
        $rootElement->setAttribute('language_tag', $language->getTag());
        $rootElement->setAttribute('language_label', $language->getLabel());

        $keyDao = $this->keyDao;

        $keys = $keyDao->findAllPrefixKeys($id);

        foreach ( $keys as $key ) /* @var $key BOL_LanguageKey */
        {
            $keyElement = $xml->createElement('key'); /* @var $keyElement DomElement */

            $keyElement->setAttribute('name', $key->getKey());

            $valDao = $this->valueDao;

            $value = $valDao->findValue($languageId, $key->getId());

            if ( $value != null )
            {
                $valueNode = $xml->createElement('value');
                $valueNode->appendChild($xml->createTextNode($value->getValue()));
                $keyElement->appendChild($valueNode);
            }

            $rootElement->appendChild($keyElement);
        }

        $xml->appendChild($rootElement);
        $XML = $xml->saveXML();

        return $XML;
    }

    /**
     * @param string $xml
     * @param bool $refreshCache
     * @param bool $importOnlyActivePluginPrefix
     * @param bool $updateValues
     * @return bool
     */
    public function importPrefix( $xml, $refreshCache=false, $importOnlyActivePluginPrefix = false, $updateValues = false )
    {
        if ( false === ( $prefixesXml = $xml->xpath("/prefix") ) )
        {
            return false;
        }

        $languageTag = (string) $prefixesXml[0]->attributes()->language_tag;
        $prefixName = strval($prefixesXml[0]->attributes()->name);

        if ( $importOnlyActivePluginPrefix && !in_array($prefixName, $this->getExceptionPrefixes()) )
        {
            $plugin = BOL_PluginService::getInstance()->findPluginByKey($prefixName);

            if ( empty($plugin) )
            {
                return false;
            }
        }
        if ( null === ( $language = $this->findByTag($languageTag) ) )
        {
            $language = new BOL_Language();
            $language->
                setTag($languageTag)->setLabel((string) $prefixesXml[0]->attributes()->language_label)
                ->setOrder($this->findMaxOrder() + 1)
                ->setStatus('inactive');

            $language->setRtl((string) $prefixesXml[0]->attributes()->language_rtl);
            $this->save($language);
        }

        if ( null === ( $prefix = $this->findPrefix($prefixName) ))
        {
            $prefix = new BOL_LanguagePrefix();

            $prefix->setPrefix($prefixName)
                ->setLabel(strval($prefixesXml[0]->attributes()->label));

            $this->savePrefix($prefix);
        }

        $keysXml = $prefixesXml[0]->xpath('child::key');

        foreach ( $keysXml as $keyXml )
        {
            if ( null === ($key = $this->findKey((string) $prefixesXml[0]->attributes()->name, (string) $keyXml->attributes()->name)) )
            {
                $key = new BOL_LanguageKey();
                $key->setKey((string) $keyXml->attributes()->name);
                $key->setPrefixId($prefix->getId());
                $this->saveKey($key);
            }

            $valueDto = $this->findValue($language->getId(), $key->getId());

            if( $valueDto !== null && !$updateValues )
            {
                continue;
            }

            if ( $valueDto === null )
            {
                $valueDto = new BOL_LanguageValue();
                $valueDto->setLanguageId($language->getId());
                $valueDto->setKeyId($key->getId());
            }

            $valueDto->setValue((string) $keyXml->value);
            $this->saveValue($valueDto, false);
        }

        if ( $refreshCache )
        {
            $this->generateCache($language->getId());
        }
    }

    /**
     * @param integer $id
     * @return BOL_Language
     */
    public function findById( $id )
    {
        return $this->languageDao->findById($id);
    }

    /**
     * @param BOL_Language $dto
     */
    public function save( BOL_Language $dto )
    {
        return $this->languageDao->save($dto);
    }

    /**
     * @return int
     */
    public function findMaxOrder()
    {
        return $this->languageDao->findMaxOrder();
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->languageDao->findAll();
    }

    /**
     * @param int $languageId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findMissingKeys( $languageId, $first, $count )
    {
        return $this->keyDao->findMissingKeys($languageId, $first, $count);
    }

    /**
     * @param int $languageId
     * @return int
     */
    public function findMissingKeyCount( $languageId )
    {
        return $this->keyDao->findMissingKeyCount($languageId);
    }

    /**
     * @param string $prefix
     * @param string $key
     * @return bool
     */
    public function isKeyUnique( $prefix, $key )
    {
        $prefixId = $this->findPrefixId($prefix);

        return $this->keyDao->findKeyId($prefixId, $key) === null;
    }

    /**
     *
     * @param string $tag
     * @return BOL_Language
     */
    public function findByTag( $tag )
    {
        return $this->languageDao->findByTag($tag);
    }

    /**
     * @param BOL_Language $language
     */
    public function delete( BOL_Language $language )
    {
        $this->valueDao->deleteValues($language->getId());
        $this->languageDao->delete($language);
        $this->generateCache($language->getId());
    }

    /**
     * @param BOL_LanguageValue $value
     * @param bool $generateCache
     */
    public function deleteValue( BOL_LanguageValue $value, $generateCache = true )
    {
        $this->valueDao->delete($value);

        if ( $generateCache === true )
        {
            $this->generateCache($value->languageId);
        }
    }

    /**
     * @param string $prefix
     * @param string $key
     * @return BOL_LanguageKey
     */
    public function findKey( $prefix, $key )
    {

        $prefixId = $this->prefixDao->findPrefixId($prefix);
        $id = $this->keyDao->findKeyId($prefixId, $key);

        if ( $id === null )
        {
            return null;
        }

        return $this->keyDao->findById($id);
    }

    /**
     * @param int $id
     * @param string $label
     * @param string $tag
     */
    public function cloneLanguage( $id, $label, $tag )
    {

        $languageClone = new BOL_Language();

        $languageClone->setLabel($label)
            ->setTag($tag)
            ->setStatus('inactive')
            ->setOrder($this->findMaxOrder() + 1);

        $this->save($languageClone);

        $prefixes = ( null == ($prefixes = $this->getPrefixList()) ) ? array() : $prefixes;

        foreach ( $prefixes as $prefix ) /* @var $prefix BOL_LanguagePrefix */
        {
            $keys = (null === $keys = $this->findAllPrefixKeys($prefix->getId())) ? array() : $keys;

            foreach ( $keys as $key )/* @var $key BOL_LanguageKey */
            {
                $value = $this->findValue($id, $key->getId()); /* @var $value BOL_LanguageValue */
                if ( $value === null )
                    continue;
                $valueClone = new BOL_LanguageValue();
                $valueClone->setKeyId($value->getKeyId())->setLanguageId($languageClone->getId())->setValue($value->getValue());
                $this->saveValue($valueClone, false);
            }
        }

        $this->generateCache($languageClone->getId());
    }

    /**
     * @param int $prefixId
     * @return array
     */
    public function findAllPrefixKeys( $prefixId )
    {
        return $this->keyDao->findAllPrefixKeys($prefixId);
    }

    /**
     * @param int $prefixId
     * @return int
     */
    public function coundAllPrefixKeys( $prefixId )
    {
        return $this->keyDao->countAllPrefixKeys($prefixId);
    }

    /**
     * @param int $languageId
     * @param int $keyId
     * @return BOL_LanguageValue
     */
    public function findValue( $languageId, $keyId )
    {
        return $this->valueDao->findValue($languageId, $keyId);
    }

    /**
     * @param BOL_LanguageValue $dto
     * @param bool $generateCache
     */
    public function saveValue( BOL_LanguageValue $dto, $generateCache = true )
    {
        $this->valueDao->save($dto);

        if ( $generateCache === true )
        {
            $this->generateCache($dto->languageId);
        }
    }

    /**
     * @return int
     */
    public function countActiveLanguages()
    {
        return $this->languageDao->countActiveLanguages();
    }

    /**
     * @param BOL_LanguagePrefix $dto
     */
    public function savePrefix( BOL_LanguagePrefix $dto )
    {
        $this->prefixDao->save($dto);
    }

    /**
     * @param string $prefix
     * @return BOL_LanguagePrefix
     */
    public function findPrefix( $prefix )
    {
        return $this->prefixDao->findByPrefix($prefix);
    }

    /**
     * @param BOL_LanguageKey $dto
     */
    public function saveKey( BOL_LanguageKey $dto )
    {
        $this->keyDao->save($dto);
    }

    /**
     * @return array
     */
    public function findActiveList()
    {
        return $this->languageDao->findActiveList();
    }

    /**
     * @param string $value
     * @return string
     */
    public function generateCustomKey( $value )
    {
        $value = trim($value);

        $len = 300;
        $key = preg_replace("/[^A-z ^0-9 ]/u", '_', ( mb_strlen($value) > $len ? mb_substr($value, 0, $len) : $value));

        $key = preg_replace("/[^\w]/u", '_', $key);

        $key = str_replace(' ', '_', $key);

        $key = preg_replace("/(_)+/u", '_', $key);

        return mb_strtolower($key);
    }

    /**
     * @return int
     */
    public function countAllKeys()
    {
        return $this->keyDao->countAll();
    }

    /**
     * @param int $id
     * @return BOL_LanguagePrefix
     */
    public function findPrefixById( $id )
    {
        return $this->prefixDao->findById($id);
    }

    /**
     * @return array
     */
    public function findAllPrefixes()
    {
        return $this->prefixDao->findAll();
    }

    /**
     * @param int $id
     * @param bool $refreshCache
     */
    public function deletePrefix( $id, $refreshCache = false )
    {
        $keys = $this->keyDao->findAllPrefixKeys($id);
        foreach ( $keys as $key )
        {
            $this->deleteKey($key->getId());
        }

        $this->prefixDao->deleteById($id);

        if ( $refreshCache )
        {
            $this->generateCacheForAllActiveLanguages();
        }
    }

    /**
     * Adds or updates new key and value
     *
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @param string $value
     * @param bool $generateCache
     *
     * @return BOL_LanguageValue
     *
     * @throws LogicException
     */
    public function addOrUpdateValue( $languageId, $prefix, $key, $value, $generateCache = true )
    {
        $prefixDto = $this->prefixDao->findByPrefix($prefix);

        if( $prefixDto == null )
        {
            throw new LogicException("Prefix `$prefix` not found!");
        }

        $keyDto = $this->findKey($prefix, $key);

        if ( $keyDto === null )
        {
            $keyDto = new BOL_LanguageKey();

            $keyDto->setPrefixId($prefixDto->id)
                ->setKey($key);

            $this->keyDao->save($keyDto);
        }

        $valueDto = $this->findValue($languageId, $keyDto->id);

        if ( $valueDto === null )
        {
            $valueDto = new BOL_LanguageValue();
        }

        $valueDto->setLanguageId($languageId)
            ->setKeyId($keyDto->getId())
            ->setValue($value);

        $this->valueDao->save($valueDto);

        if ( $generateCache )
        {
            $this->generateCache($valueDto->languageId);
        }

        return $valueDto;
    }

    /**
     * @param string $path
     * @param bool $refreshCache
     * @param bool $addLanguage
     * @param bool $updateValues
     */
    public function importPrefixFromDir( $path, $refreshCache = true, $addLanguage = false, $updateValues = false )
    {
        $path = UTIL_File::removeLastDS($path) .DS;

        if ( !UTIL_File::checkDir($path) )
        {
            throw new InvalidArgumentException( "Directory not found : {$path}" );
        }

        $arr = glob("{$path}*");

        $prefixesToImport = array();
        $langsToImport = array();

        foreach ( $arr as $index => $dir )
        {
            $dh = opendir($dir);

            if ( !file_exists($dir . DS . 'language.xml') )
            {
                continue;
            }

            $langXmlE = simplexml_load_file($dir . DS . 'language.xml');

            $l = array('label' => strval($langXmlE->attributes()->label), 'tag' => strval($langXmlE->attributes()->tag), 'path' => $dir . DS);

            if ( !in_array($l, $langsToImport) )
            {
                $langsToImport[] = $l;
            }

            /* @var $xmlElement SimpleXMLElement */
            while ( false !== ( $file = readdir($dh) ) )
            {
                if ( $file == '.' || $file == '..' || is_dir($dir.DS.$file) || $file == 'language.xml' || !file_exists($dir.DS.$file) )
                {
                    continue;
                }

                $xmlElement = simplexml_load_file($dir.DS.$file);
                $tmp = $xmlElement->xpath('/prefix');
                $prefixElement = $tmp[0];

                $prefixItem = array(
                    'label' => strval($prefixElement->attributes()->label),
                    'prefix' => strval($prefixElement->attributes()->name)
                );

                if ( !in_array($prefixItem, $prefixesToImport) )
                {
                    $prefixesToImport[] = $prefixItem;
                }
            }
        }

        $languages = $this->getLanguages();

        $activateFirstLang = empty($languages);

        foreach ( $langsToImport as $langToImport )
        {
            if ( !$this->findByTag($langToImport['tag']) )
            {
                if ( !$addLanguage )
                {
                    continue;
                }

                $dto = new BOL_Language();
                $dto->setLabel($langToImport['label'])
                    ->setTag($langToImport['tag'])
                    ->setStatus( ($activateFirstLang ? 'active' : 'inactive') )
                    ->setOrder($this->findMaxOrder() + 1);
                $this->save($dto);

                $activateFirstLang = false;
            }

            foreach ( $prefixesToImport as $prefixToImport )
            {
                $filePath = $langToImport['path'] . "{$prefixToImport['prefix']}.xml";

                if ( !file_exists($filePath) )
                {
                    continue;
                }

                $xml = simplexml_load_file($filePath);
                $this->importPrefix($xml, false, false, $updateValues);
            }
        }

        if ( $refreshCache )
        {
            $this->generateCacheForAllActiveLanguages();
        }
    }

    public function importPrefixFromZip($path, $key, $refreshCache = true, $addLanguage = false, $updateValues = false )
    {
        $importDir = $this->getImportDirPath() . $key . DS;
        @mkdir($importDir);

        chmod($importDir, 0777);

        $this->cleanImportDir($importDir);

        $zip = new ZipArchive();

        $zip->open($path);

        $zip->extractTo($importDir);

        $zip->close();

        $langsDir = $importDir;

        if ( file_exists($importDir . 'langs') )
        {
            $langsDir = $importDir . 'langs' . DS;
        }

        $this->importPrefixFromDir($langsDir, $refreshCache, $addLanguage, $updateValues);

        UTIL_File::removeDir($importDir);
    }

    /**
     *
     * @return BOL_Language
     */
    public function findDefault()
    {
        return $this->languageDao->getCurrent();
    }

    /**
     *
     * @return BOL_Language
     */
    public function getCurrent()
    {
        if ( $this->currentLanguage === null )
        {
            $this->currentLanguage = $this->languageDao->getCurrent();
        }

        return $this->currentLanguage;
    }

    /**
     * @param BOL_Language $language
     * @param bool $loadFromCache     *
     */
    public function setCurrentLanguage( BOL_Language $language, $loadFromCache = true )
    {
        $this->currentLanguage = $language;

        if ( $loadFromCache )
        {
            $this->loadFromCache();
        }
    }

    /* ---------------------- TODO  replace logic with temp dirs -------------------- */

    public static function getImportDirPath()
    {
        return OW::getPluginManager()->getPlugin('admin')->getPluginFilesDir() . 'languages' . DS . 'import' . DS;
    }

    public static function getExportDirPath()
    {
        return OW::getPluginManager()->getPlugin('admin')->getPluginFilesDir() . 'languages' . DS . 'export' . DS;
    }

    public static function getTmpDirPath()
    {
        return OW::getPluginManager()->getPlugin('admin')->getPluginFilesDir() . 'languages' . DS . 'tmp' . DS;
    }

    private function cleanImportDir( $dir )
    {
        $dh = opendir($dir);

        while ( ( $node = readdir($dh) ) )
        {
            if ( $node == '.' || $node == '..' )
                continue;

            if ( is_dir($dir . $node) )
            {
                UTIL_File::removeDir($dir . $node);
                continue;
            }

            unlink($dir . $node);
        }
    }

    /* ---------------------- \TODO  replace logic with temp dirs -------------------- */

    /**
     * @return array
     */
    public function getExceptionPrefixes()
    {
        return $this->exceptionPrefixes;
    }


    protected function loadFromCache()
    {
        $allLanguages = $this->getLanguages();

        // exit if no active languages
        if ( empty($allLanguages) ) {
            return;
        }

        $filename = $this->getLanguageCacheDir() . $this->getCacheFilename($this->getCurrent()->getId());
        $language = array();

        // include cache file
        include $filename;

        $this->language = $language;
    }

    protected function getCacheFilename( $languageId )
    {
        return "lang_{$languageId}.php";
    }

    protected function getLanguageCacheDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getPluginFilesDir();
    }

    /**
     * @param $prefix string
     * @return bool
     */
    protected function prefixExists( $prefix )
    {
        $prefixDto = $this->prefixDao->findPrefixId($prefix);

        return ( $prefixDto !== null);
    }
}
