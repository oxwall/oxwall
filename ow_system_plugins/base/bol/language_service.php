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
    private static $currentLanguage = null;
    private $languageCacheDir; // = OW_DIR_ROOT.'ow_plugin_files'.DS;
    private $language = array();
    private $languageDao;
    private $prefixDao;
    private $keyDao;
    private $valueDao;
    private $exceptionPrefixes = array( "mobile", "nav", "ow_custom" ); // section which importing without checking plugin key

    private function __construct( $includeCache = true )
    {
        $this->languageDao = BOL_LanguageDao::getInstance();
        $this->prefixDao = BOL_LanguagePrefixDao::getInstance();
        $this->keyDao = BOL_LanguageKeyDao::getInstance();
        $this->valueDao = BOL_LanguageValueDao::getInstance();
        $this->languageCacheDir = OW::getPluginManager()->getPlugin('base')->getPluginFilesDir();

        if ( $includeCache )
        {
            $this->loadFromCahce();
        }
    }

    private function loadFromCahce()
    {
        $filename = $this->languageCacheDir . $this->getCacheFilename($this->getCurrent()->getId());
        $language = array();

        // include cache file
        include $filename;

        $this->language = $language;
    }

    private function getCacheFilename( $languageId )
    {
        return "lang_{$languageId}.php";
    }

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
        $filename = $this->languageCacheDir . 'lang_' . $languageId . '.php';

        file_put_contents($filename, $cacheContent);
        @chmod($filename, 0666);

        $this->loadFromCahce();
    }

    public function generateCacheForAllActiveLanguages()
    {
        $langs = $this->findActiveList();
        foreach ( $langs as $lang )
        {
            /* @var $lang BOL_Language */
            $this->generateCache($lang->id);
        }
    }
    /**
     * Class instance
     *
     * @var BOL_LanguageService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageService
     */
    public static function getInstance( $includeCache = true )
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self($includeCache);
        }

        return self::$classInstance;
    }

    /**
     * Adds new key and value
     *
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @param string $value
     *
     * @return BOL_LanguageValue
     *
     * @throws 'Such prefix doesn\'t exist..' Exception
     */
    public function addValue( $languageId, $prefix, $key, $value, $generateCache = true )
    {
        $list = array();

        $prefixDto = $this->prefixDao->findByPrefix($prefix);

        $prefixId = $prefixDto->id;

        $keyDao = $this->keyDao;

        if ( null === ( $keyDto = $this->findKey($prefix, $key) ) )
        {
            $keyDto = new BOL_LanguageKey();

            $keyDto->setPrefixId($prefixId)
                ->setKey($key);

            $keyDao->save($keyDto);
        }

        $valueDto = new BOL_LanguageValue();

        $valueDto->setLanguageId($languageId)
            ->setKeyId($keyDto->getId())
            ->setValue($value);

        $valueDao = $this->valueDao;
        $valueDao->save($valueDto);

        if ( $generateCache === true )
        {
            $this->generateCache($valueDto->languageId);
        }

        return $valueDto;
    }

    /**
     * Adds or updates new key and value
     *
     * @param int $languageId
     * @param string $prefix
     * @param string $key
     * @param string $value
     *
     * @return BOL_LanguageValue
     *
     * @throws 'Such prefix doesn\'t exist..' Exception
     */
    public function addOrUpdateValue( $languageId, $prefix, $key, $value, $generateCache = true )
    {
        $prefixDto = $this->prefixDao->findByPrefix($prefix);

        if ( null === ( $keyDto = $this->findKey($prefix, $key) ) )
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

        if ( $generateCache === true )
        {
            $this->generateCache($valueDto->languageId);
        }

        return $valueDto;
    }

    /**
     * Adds new Prefix
     *
     * @throws 'Duplicated prefix..' exception
     */
    public function addPrefix( $prefix, $label )
    {
        if ( self::isPrefixExist($prefix) )
        {
            throw new Exception('Duplicated prefix..');
        }

        $prefixDto = new BOL_LanguagePrefix();

        $prefixDto->setPrefix($prefix)
            ->setLabel($label);

        $this->prefixDao->save($prefixDto);

        return $prefixDto;
    }

    private function isPrefixExist( $prefix )
    {
        $prefixDto = $this->prefixDao->findPrefixId($prefix);

        return ( $prefixDto !== null);
    }

    public function findLastKeyList( $first, $count, $prefix = null )
    {
        return $this->valueDao->findLastKeyList($first, $count, $prefix);
    }

    public function countKeyByPrefix( $prefix )
    {
        $keyDao = BOL_LanguageKeyDao::getInstance();

        $prefixId = $this->findPrefixId($prefix);

        return $keyDao->countKeyByPrefix($prefixId);
    }

    public function getText( $languageId, $prefix, $key )
    {
        OW::getEventManager()->trigger( new OW_Event('servicelangtools.lang_used_log', array( 'prefix' => $prefix, 'key' => $key)) );
        
        return ( isset($this->language[$languageId][$prefix . '+' . $key]) ) ? $this->language[$languageId][$prefix . '+' . $key] : null;
    }

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

    public function getPrefixList()
    {
        return $this->prefixDao->findAll();
    }

    public function getLanguages()
    {
        return $this->languageDao->findAll();
    }

    public function findSearchResultKeyList( $languageId, $first, $count, $search )
    {
        return $this->valueDao->findSearchResultKeyList($languageId, $first, $count, $search);
    }

    public function countSearchResultKeys( $languageId, $search )
    {
        return $this->valueDao->countSearchResultKeys($languageId, $search);
    }

    public function findKeySearchResultKeyList( $languageId, $first, $count, $search )
    {
        return $this->valueDao->findKeySearchResultKeyList($languageId, $first, $count, $search);
    }

    public function countKeySearchResultKeys( $languageId, $search )
    {
        return $this->valueDao->countKeySearchResultKeys($languageId, $search);
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

        BOL_LanguageKeyDao::getInstance()->save($dto);

        return $dto;
    }

    public function findPrefixId( $prefix )
    {
        return $this->prefixDao->findPrefixId($prefix);
    }

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

    public function exportLanguage( $languageId )
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        $rootElement = $xml->createElement("language");

        $prefixes = $this->getPrefixList();

        /* @var $prefix BOL_LanguagePrefix */
        foreach ( $prefixes as $prefix )
        {
            /* @var $prefixElement DOMElement */
            $prefixElement = $xml->createElement('prefix');
            $prefixElement->setAttribute('name', $prefix->getPrefix());
            $rootElement->appendChild($prefixElement);
        }

        $xml->appendChild($rootElement);
    }

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

    public function getPrefixXML( $id, $languageId )
    {

        /* @var $prefix BOL_LanguagePrefix */
        if ( ($prefix = BOL_LanguagePrefixDao::getInstance()->findById($id)) == null )
            throw new Exception("Prefix with id: {$id}, doesn't exist");

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

    public function importPrefix( $xml, $refreshCache=false, $importOnlyActivePluginPrefix = false )
    {
        if ( false === ( $prefixesXml = $xml->xpath("/prefix") ) )
        {
            return false;
        }

        $service = $this;

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
        if ( null === ( $language = $service->findByTag($languageTag) ) )
        {
            $language = new BOL_Language();
            $language->
                setTag($languageTag)->setLabel((string) $prefixesXml[0]->attributes()->language_label)
                ->setOrder($service->findMaxOrder() + 1)
                ->setStatus('inactive');

            $language->setRtl((string) $prefixesXml[0]->attributes()->language_rtl);

            $service->save($language);
        }

        if ( null === ( $prefix = $service->findPrefix($prefixName) ))
        {
            $prefix = new BOL_LanguagePrefix();

            $prefix->setPrefix($prefixName)
                ->setLabel(strval($prefixesXml[0]->attributes()->label));

            $service->savePrefix($prefix);
        }

        $keysXml = $prefixesXml[0]->xpath('child::key');

        foreach ( $keysXml as $keyXml )
        {

            if ( null === ($key = $service->findKey((string) $prefixesXml[0]->attributes()->name, (string) $keyXml->attributes()->name)) )
            {
                $key = new BOL_LanguageKey();
                $key->setKey((string) $keyXml->attributes()->name)->
                    setPrefixId($prefix->getId());
                $service->saveKey($key);
            }

            if ( null === ( $value = $service->findValue($language->getId(), $key->getId()) ) )
            {
                $value = new BOL_LanguageValue();
                $value->setLanguageId($language->getId())->
                    setKeyId($key->getId())->
                    setValue((string) $keyXml->value);

                $service->saveValue($value, false);
            }
            
            if ( $refreshCache )
            {
                $this->generateCache($language->getId());
            }
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

    public function save( $dto )
    {
        return $this->languageDao->save($dto);
    }

    public function findMaxOrder()
    {
        return $this->languageDao->findMaxOrder();
    }

    /**
     *
     * @return BOL_Language
     */
    public function getCurrent()
    {
        //printVar(self::$currentLanguage);
        if ( self::$currentLanguage === null )
        {
            if ( !empty($_GET['language_id']) )
            {
                OW::getSession()->set('base.language_id', $_GET['language_id']);
            }
            else if ( !empty($_COOKIE['base_language_id']) )
            {
                OW::getSession()->set('base.language_id', $_COOKIE['base_language_id']);
            }

            $session_language_id = OW::getSession()->get('base.language_id');
            //printVar($session_language_id);
            if ( !empty($session_language_id) )
            {
                /* @var $languageDto BOL_Language */
                $languageDto = $this->findById($session_language_id);

                if ( $languageDto === null || $languageDto->getStatus() !== 'active' )
                {
                    $languageDto = $this->languageDao->getCurrent();
                }
            }
            else
            {
                $languageDto = $this->languageDao->getCurrent();
            }

            setcookie('base_language_id', (string) $languageDto->getId(), time() + 60 * 60 * 24 * 30, "/");

            $this->setCurrentLanguage($languageDto, false);
        }
        //printVar(self::$currentLanguage);
        return self::$currentLanguage;
    }
    
    public function setCurrentLanguage( BOL_Language $language, $loadFromCache = true )
    {
        self::$currentLanguage = $language;
        
        if ( $loadFromCache )
        {
            $this->loadFromCahce();
        }
    }

    public function resetCurrentLanguage()
    {
        unset($_COOKIE['base_language_id']);
        OW::getSession()->delete('base.language_id');
        self::$currentLanguage = null;

        $this->getCurrent();
    }

    public function findAll()
    {
        return $this->languageDao->findAll();
    }

    public function findMissingKeys( $languageId, $first, $count )
    {
        return $this->keyDao->findMissingKeys($languageId, $first, $count);
    }

    public function findMissingKeyCount( $languageId )
    {
        return $this->keyDao->findMissingKeyCount($languageId);
    }

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

    public function delete( BOL_Language $language )
    {

        $this->valueDao->deleteValues($language->getId());
        $this->languageDao->delete($language);
        $this->generateCache($language->getId());
    }

    public function deleteValue( BOL_LanguageValue $value, $generateCache = true )
    {
        $this->valueDao->delete($value);

        if ( $generateCache === true )
        {
            $this->generateCache($value->languageId);
        }
    }

    /**
     *
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

    public function findAllPrefixKeys( $prefixId )
    {
        return $this->keyDao->findAllPrefixKeys($prefixId);
    }

    public function coundAllPrefixKeys( $prefixId )
    {
        return $this->keyDao->countAllPrefixKeys($prefixId);
    }

    /**
     *
     * @param int $languageId
     * @param int $keyId
     * @return BOL_LanguageValue
     */
    public function findValue( $languageId, $keyId )
    {
        return $this->valueDao->findValue($languageId, $keyId);
    }

    public function saveValue( BOL_LanguageValue $dto, $generateCache = true )
    {
        $this->valueDao->save($dto);

        if ( $generateCache === true )
        {
            $this->generateCache($dto->languageId);
        }
    }

    public function countActiveLanguages()
    {
        return $this->languageDao->countActiveLanguages();
    }

    public function savePrefix( $dto )
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

    public function saveKey( $dto )
    {
        $this->keyDao->save($dto);
    }

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

    public function findActiveList()
    {

        return $this->languageDao->findActiveList();
    }

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

    public function countAllKeys()
    {
        return $this->keyDao->countAll();
    }

    public function findPrefixById( $id )
    {
        return $this->prefixDao->findById($id);
    }

    public function findAllPrefixes()
    {
        return $this->prefixDao->findAll();
    }

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

    public function importPrefixFromZip( $path, $key, $refreshCache=true )
    {
        $importDir = $this->getImportDirPath() . $key . DS;

        mkdir($importDir);

        chmod($importDir, 0777);

        $this->cleanImportDir($importDir);

        $zip = new ZipArchive();

        $zip->open($path);

        $zip->extractTo($importDir);

        $zip->close();

        $arr = glob("{$importDir}language_*");

        $langsToImport = array();
        $prefixesToImport = array();

        foreach ( $arr as $index => $dir )
        {
            $dh = opendir($dir);

            $langXmlE = simplexml_load_file($dir . DS . 'language.xml'); /* @var $xmlElement SimpleXMLElement */

            $l = array('label' => strval($langXmlE->attributes()->label), 'tag' => strval($langXmlE->attributes()->tag));

            if ( !in_array($l, $langsToImport) )
                $langsToImport[] = $l;

            while ( false !== ( $file = readdir($dh) ) )
            {
                if ( $file == '.' || $file == '..' )
                    continue;

                if ( is_dir("{$dir}/{$file}") )
                {
                    //printVar("$file/");
                }
                else
                {
                    if ( $file == 'language.xml' )
                    {
                        continue;
                    }

                    $xmlElement = simplexml_load_file("{$dir}/{$file}"); /* @var $xmlElement SimpleXMLElement */

                    $arr = $xmlElement->xpath('/prefix/key');
                    $tmp = $xmlElement->xpath('/prefix');

                    $prefixElement = $tmp[0];

                    $p = array('label' => strval($prefixElement->attributes()->label), 'prefix' => strval($prefixElement->attributes()->name));

                    if ( !in_array($p, $prefixesToImport) )
                        $prefixesToImport[] = $p;
                }
            }
        }

        foreach ( $langsToImport as $langToImport )
        {
            if ( !$this->findByTag($langToImport['tag']) )
            {
                continue;
            }

            foreach ( $prefixesToImport as $prefixToImport )
            {
                $xml = simplexml_load_file($importDir . "language_{$langToImport['tag']}" . DS . "{$prefixToImport['prefix']}.xml");

                $this->importPrefix($xml, false);
            }

            if ( $refreshCache )
            {
                $this->generateCacheForAllActiveLanguages();
            }
        }

        UTIL_File::removeDir($importDir);
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
    
    public function getExceptionPrefixes()
    {
        return $this->exceptionPrefixes;
    }
}