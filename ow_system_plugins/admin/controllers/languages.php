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
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_Languages extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_LanguageService
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = BOL_LanguageService::getInstance();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_SETTINGS)->getElement('sidebar_menu_item_settings_language')->setActive(true);

        $this->setPageHeading(OW::getLanguage()->text('admin', 'languages_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_edit');

        $this->assign('devMode', $this->isDevMode());
        $this->addComponent('menu', $this->getMenu());
    }

    public function getMenu()
    {
        $items = array();
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'edit_language'));
        $item->setIconClass('ow_ic_edit');
        $item->setKey('edit_lang');

        if ( $this->isDevMode() )
        {
            $item->setUrl(OW::getRouter()->urlForRoute('admin_developer_tools_language'));
        }
        else
        {
            $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_language'));
        }

        $item->setOrder(1);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'available_languages'));
        $item->setIconClass('ow_ic_files');
        $item->setKey('avail_lang');

        if ( $this->isDevMode() )
        {
            $item->setUrl(OW::getRouter()->urlForRoute('admin_developer_tools_language_mod'));
        }
        else
        {
            $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_language_mod'));
        }

        $item->setOrder(2);
        $items[] = $item;

        return new BASE_CMP_ContentMenu($items);
    }

    private function getImportFilePatch($tag, $prefix)
    {
        $path = BOL_LanguageService::getInstance()->getImportDirPath();

        $filepath = $path . "language_{$tag}" . DS . "{$prefix}.xml";

        if ( file_exists($path . 'langs' . DS) )
        {
            $filepath = $path . 'langs' . DS . "{$tag}" . DS . "{$prefix}.xml";
        }

        return $filepath;
    }

    public function import()
    {
        $service = BOL_LanguageService::getInstance();

        if ( !empty($_POST['set']['lang']) && count($_POST['set']['lang']) > 0 )
        {
            switch ( $_POST['imp-type'] )
            {
                case 'pack':

                    foreach ( $_POST['set']['lang'] as $key => $value )
                    {
                        $tag = str_replace('lang_', '', $key);

                        foreach ( $value as $prefix )
                        {
                            $xml = simplexml_load_file($this->getImportFilePatch($tag, $prefix));
                            $service->importPrefix($xml, false, true);
                        }

                        $language = $service->findByTag($tag);
                        $service->generateCache($language->getId());
                    }

                    break;

                case 'single-xml':

                    $keys = array_keys($_POST['set']['lang']);

                    $tag = str_replace('lang_', '', $keys[0]);

                    $prefix = $_POST['set']['lang']["lang_{$tag}"][0];
                    $xml = simplexml_load_file($service->getImportPath() . "{$prefix}.xml");

                    $service->importPrefix($xml, true, true);

                    break;
            }
        }

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'language_import_complete_success_message'));

        $this->redirectToAction('mod');
    }

    public function index()
    {
        $languageService = BOL_LanguageService::getInstance();

        if ( empty($_GET['language']) )
        {
            $language = $languageService->getCurrent();
        }
        else
        {
            $language = $languageService->findByTag($_GET['language']);
        }

        $this->assign('label', $language->getLabel());
        $this->assign('tag', $language->getTag());

        $current = $languageService->getCurrent();

        $this->assign('origLabel', $current->getLabel());
        $this->assign('origTag', $current->getTag());

        $this->assign('languageSwitchUrl', OW::getRequest()->buildUrlQueryString(null, array('language' => null)));

        $this->assign('lang_switch_url', OW::getRequest()->buildUrlQueryString(null, array('langId' => null, 'page' => null)));

        $this->assign('section_switch_url', OW::getRequest()->buildUrlQueryString(null, array('prefix' => null, 'page' => null)));

        $this->assign('searchFormActionUrl', OW::getRequest()->buildUrlQueryString(null,
            array('prefix' => ((!empty($_GET['prefix'])) ? $_GET['prefix'] : null), 'language' => ((!empty($_GET['language'])) ? $_GET['language'] : null), 'search' => null, 'page' => null, 'in_keys' => null))
        );

        $this->assign('langs', $languageService->getLanguages());
        $this->assign('language', $language);

        if ( isset($_POST['command']) && $_POST['command'] == 'edit-values' )
        {
            $arr = empty($_POST['values']) ? array() : $_POST['values'];

            foreach ( $arr as $key => $value )
            {
                if ( strlen($value) < 1 )
                {
                    continue;
                }
                /* @var $entity BOL_LanguageValue */
                $entity = $languageService->findValue($language->getId(), $key);

                $entity->setValue($value);
                $languageService->saveValue($entity, false);
            }

            $arr = empty($_POST['missing']) ? array() : $_POST['missing'];

            foreach ( $arr as $prefixStr => $value )
            {
                foreach ( $value as $key2 => $value2 )
                {
                    if ( strlen(trim($value2)) == 0 )
                    {
                        continue;
                    }

                    $keyDto = $languageService->findKey($prefixStr, $key2);

                    $dto = new BOL_LanguageValue();

                    $dto->setLanguageId($language->getId())->setValue($value2)->setKeyId($keyDto->getId());

                    $languageService->saveValue($dto, false);
                }
            }

            $languageService->generateCache($language->getId());
            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'languages_values_updated'));
            $this->redirect();
        }

        $this->assign('prefixes', $languageService->getPrefixList());

        $this->assign('current_prefix', ( empty($_GET['prefix']) ? '' : $_GET['prefix']));

        $this->assign('current_search', ( isset($_GET['search']) && strlen($_GET['search']) ) ? $_GET['search'] : 'Search..');

        $this->assign('isSearchResults', ( (empty($_GET['search'])) ? false : true));


        $page = (empty($_GET['page'])) ? 1 : $_GET['page'];

        $rpp = 20;

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        if ( isset($_GET['search']) && strlen($_GET['search']) )
        {
            $search = $_GET['search'];

            if ( !empty($_GET['in_keys']) )
            {
                $this->assign('searchInKeys', 'y');

                $list = $this->getReordered($languageService->findKeySearchResultKeyList($language->getId(), $first, $count, $search), $language->getId());

                $item_count = $languageService->countKeySearchResultKeys($language->getId(), $search);
            }
            else
            {
                $list = $this->getReordered($languageService->findSearchResultKeyList($language->getId(), $first, $count, $search), $language->getId());

                $item_count = $languageService->countSearchResultKeys($language->getId(), $search);
            }
        }
        else if ( !empty($_GET['prefix']) )
        {
            $prefix = $_GET['prefix'];

            switch ( $prefix )
            {
                case 'missing-text':

                    $list = $this->getReordered(
                        $languageService->findMissingKeys($language->getId(), $first, $count),
                        $language->getId());

                    $item_count = $languageService->findMissingKeyCount($language->getId());

                    break;

                case 'all':

                    $list = $this->getReordered(
                        $languageService->findLastKeyList($first, $count),
                        $language->getId());

                    $item_count = $languageService->countAllKeys();

                    break;

                default:

                    $list = $this->getReordered($languageService->findLastKeyList($first, $count, $prefix), $language->getId());
                    $item_count = $languageService->countKeyByPrefix($prefix);

                    break;
            }
        }
        else
        {
            $list = $this->getReordered(
                $languageService->findLastKeyList($first, $count),
                $language->getId());

            $item_count = $languageService->countAllKeys();
        }

        $pages = ceil($item_count / 20);

        $paging = new BASE_CMP_Paging($page, $pages, 5);

        $this->assign('paging', $paging->render());

//~~

        $this->assign('list', $list);

        $prefixes = $languageService->getPrefixList();

        $this->assign('prefixes', $prefixes);

        $this->addForm(new AddKeyForm($prefixes, $language, $this->isDevMode()));
    }

    private function getReordered( array $set, $languageId )
    {
        $languageService = BOL_LanguageService::getInstance();

        $current = $languageService->getCurrent();

        $result = array();

        $i = 0;

        $indexes = array();

        foreach ( $set as $value )
        {
            if ( !array_key_exists($value['prefix'], $indexes) )
            {
                $indexes[$value['prefix']] = ++$i;

                $index = $indexes[$value['prefix']];

                $prefix = $value['prefix'];

                $result[$index] = array(
                    'prefix' => $prefix,
                    'label' => $value['label'],
                    'keys' => array(),);
            }

            $key = $value['key'];

            $text = $languageService->getValue($languageId, $prefix, $key);


            $origText = $languageService->getValue($current->getId(), $prefix, $key);

            $origText = ($origText !== null) ? $origText : '';

            $result[$index]['data'][] = array('key' => $key, 'value' => $text, 'origValue' => $origText);
        }

        return $result;
    }

    private function getImportPath()
    {
        $path = BOL_LanguageService::getInstance()->getImportDirPath();

        if ( file_exists($path . 'langs' . DS) )
        {
            $path = $path . 'langs' . DS;
        }

        return $path;
    }

    private function setImportInfo()
    {
        $service = BOL_LanguageService::getInstance();

        $langsToImport = array();
        $prefixesToImport = array();

        $path = $this->getImportPath();

        $arr = glob("{$path}*");
        $type = '';

        if ( !empty($arr) )
        {
            $type = 'pack';

            $flag = false;

            foreach ( $arr as $index => $dir )
            {
                $dh = opendir($dir);

                if ( !file_exists($dir . DS . 'language.xml') )
                {
                    continue;
                }

                $langXmlE = simplexml_load_file($dir . DS . 'language.xml'); /* @var $xmlElement SimpleXMLElement */

                $l = array('label' => strval($langXmlE->attributes()->label), 'tag' => strval($langXmlE->attributes()->tag));

                if ( !in_array($l, $langsToImport) )
                {
                    $langsToImport[] = $l;
                }

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

                        $prefix = strval($prefixElement->attributes()->name);

                        if(!in_array($prefix, BOL_LanguageService::getInstance()->getExceptionPrefixes()))
                        {
                            $plugin = BOL_PluginService::getInstance()->findPluginByKey($prefix);

                            if ( empty($plugin) )
                            {
                                continue;
                            }
                        }

                        $p = array('label' => strval($prefixElement->attributes()->label), 'prefix' => $prefix);
                        if ( !in_array($p, $prefixesToImport) )
                            $prefixesToImport[] = $p;
                    }
                }

                $flag = true;
            }

            if ( !$flag )
            {
                throw new LogicException();
            }
        }
        else
        {
            $type = "single-xml";
            $arr = glob("{$path}*.xml");

            if ( empty($arr) || !file_exists($arr[0]) )
            {
                throw new LogicException();
            }

            $xmlElement = simplexml_load_file($arr[0]);

            if ( !$xmlElement )
            {
                throw new LogicException();
            }

            $tmp = $xmlElement->xpath('/prefix');

            $prefixElement = $tmp[0];

            $plugin = BOL_PluginService::getInstance()->findPluginByKey(strval($prefixElement->attributes()->name));

            if ( !empty($plugin) )
            {
                $l = array(
                    'tag' => strval($prefixElement->attributes()->language_tag),
                    'label' => strval($prefixElement->attributes()->language_label),
                );
                $langsToImport[] = $l;

                $prefixesToImport[] = array(
                    'label' => $prefixElement->attributes()->label,
                    'prefix' => $prefixElement->attributes()->name,
                );
            }
        }

        $this->assign('langsToImport', $langsToImport);
        $this->assign('prefixesToImport', $prefixesToImport);
        $this->assign('type', $type);
    }

    private function cleanImportDir( $dir )
    {
        $dh = opendir($dir);

        while ( false !== ( $node = readdir($dh) ) )
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

    public function deleteKey()
    {
        $languageService = BOL_LanguageService::getInstance();

        $key = $_GET['key'];
        $prefix = $_GET['prefix'];

        $dto = $languageService->findKey($prefix, $key);

        if ( !empty($dto) ) {
            $languageService->deleteKey($dto->getId());
            OW::getFeedback()->info('Deleted');
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function langEditFormResponder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( OW::getRequest()->isPost() )
        {
            if ( trim($_POST['form_name']) === 'lang_edit' && !empty($_POST['langId']) && !empty($_POST['label']) && !empty($_POST['tag']) )
            {
                $language = $this->service->findById((int) $_POST['langId']);

                if ( $language !== null )
                {
                    if ( $_POST['tag'] != $language->tag )
                    {
                        $tmpLanguage = $this->service->findByTag($_POST['tag']);

                        if ( !empty($tmpLanguage) )
                        {
                            exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('admin', 'msg_lang_invalid_language_tag'))));
                        }
                    }

                    $language->setLabel(trim($_POST['label']));
                    $language->setTag(trim($_POST['tag']));

                    if ( !empty($_POST['rtl']) )
                    {
                        $language->setRtl(true);
                    }
                    else
                    {
                        $language->setRtl(false);
                    }

                    $event = new OW_Event('admin.before_save_lang_value', array('dto'=>$language));
                    OW::getEventManager()->trigger($event);

                    $this->service->save($language);

                    exit(json_encode(array('result' => true, 'message' => OW::getLanguage()->text('admin', 'language_edit_form_success_message'))));
                }
            }
        }

        exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('admin', 'language_edit_form_error_message'))));
    }

    public function mod()
    {
        $languageService = BOL_LanguageService::getInstance();

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');
        }

        if ( isset($_POST['command']) && $_POST['command'] == 'upload-lp' )
        {
            if ( empty($_FILES['file']) || (int) $_FILES['file']['error'] !== 0 || !is_uploaded_file($_FILES['file']['tmp_name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('admin', 'add_language_pack_empty_file_error_message'));
                $this->redirect();
            }

            $this->cleanImportDir($languageService->getImportDirPath());

            $tmpName = $_FILES['file']['tmp_name'];

            $uploadFilePath = $languageService->getImportDirPath() . $_FILES['file']['name'];
            move_uploaded_file($tmpName, $uploadFilePath);

            if ( file_exists($tmpName) )
            {
                unlink($tmpName);
            }

            switch ( true )
            {
                case preg_match('/\.xml/', $_FILES['file']['name']):

                    break;

                case preg_match('/\.zip/', $_FILES['file']['name']):

                    $zip = new ZipArchive();

                    $opened = $zip->open($uploadFilePath);

                    if ( !$opened )
                    {
                        @unlink($uploadFilePath);
                        OW::getFeedback()->error(OW::getLanguage()->text('admin', 'add_language_pack_empty_file_error_message'));
                        $this->redirect();
                    }

                    $zip->extractTo($languageService->getImportDirPath());
                    $zip->close();

                    @unlink($uploadFilePath);

                    break;

                default:
                    @unlink($uploadFilePath);
                    OW::getFeedback()->error(OW::getLanguage()->text('admin', 'add_language_pack_empty_file_error_message'));
                    $this->redirect();
            }

            try
            {
                $this->setImportInfo();
            }
            catch ( LogicException $e )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('admin', 'add_language_pack_empty_file_error_message'));
                @unlink($uploadFilePath);
                $this->redirect();
            }
        }

        $this->assign('foo', ( isset($_POST['command']) && $_POST['command'] == 'upload-lp'));

        if ( isset($_POST['command']) && $_POST['command'] === 'export-langs' )
        {
            $za = new ZipArchive();

            $archiveName = 'lang-dump-' . date('d-m-y') . '.zip';
            $archivePath = $languageService->getTmpDirPath() . $archiveName;

            $za->open($archivePath, ZIPARCHIVE::CREATE);

            foreach ( $_POST['set']['lang'] as $key => $value )
            {
                $langId = intval(str_replace('lang_', '', $key));

                if ( !is_int($langId) || $langId <= 0 )
                    continue;

                $langDto = $languageService->findById($langId); /* @var $langDto BOL_Language */

                //$langDir = 'langs' . DS . $langDto->getTag() . DS;
                $langDir = "language_{$langDto->getTag()}" . DS;
                $za->addEmptyDir($langDir);

                $dir = "{$languageService->getExportDirPath()}{$langDir}";

                if ( !is_dir($dir) )
                {
                    mkdir($dir, 0777, true);
                }

                $file = $dir . "{$langDto->getTag()}.xml";
                $fd = fopen($file, 'w');
                $xml = $languageService->getLanguageXML($langDto->getId());
                fwrite($fd, $xml);
                $za->addFile($file, $langDir . "language.xml");

                foreach ( $value as $prefixId )
                {
                    /* @var $prefixDto BOL_LanguagePrefix */
                    $prefixDto = $languageService->findPrefixById($prefixId);

                    $xml = $languageService->getPrefixXML($prefixId, $langId);
                    $file = $dir . "{$prefixDto->getPrefix()}.xml";
                    $fd = fopen($file, 'w');
                    fwrite($fd, $xml);

                    $za->addFile($file, $langDir . "{$prefixDto->getPrefix()}.xml");
                }
            }

            $za->close();

            if ( file_exists($archivePath) )
            {
                ob_end_clean();
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=' . $archiveName);
                header('Content-Transfer-Encoding: binary');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($archivePath));
                readfile($archivePath);
                @unlink($archivePath);
                exit;
            }
        }

        $ls = $languageService->findAll();

        $ls = empty($ls) ? array() : $ls;

        function lCmp( $a, $b )
        {
            return ( $a->getOrder() > $b->getOrder() ) ? 1 : -1;
        }
        uasort($ls, 'lCmp');

        $active_langs = array();
        $inactive_langs = array();

        foreach ( $ls as $l )
        {
            switch ( $l->getStatus() )
            {
                case 'active':
                    $active_langs[] = array(
                        'id' => $l->getId(),
                        'label' => $l->getLabel(),
                        'isDefault' => ( $l->getOrder() == 1 ? true : false ),
                        'tag' => $l->getTag(),
                        'missing_key_count' => $languageService->findMissingKeyCount($l->getId()));

                    break;

                case 'inactive':
                    $inactive_langs[] = array(
                        'id' => $l->getId(),
                        'label' => $l->getLabel(),
                        'tag' => $l->getTag(),
                        'isDefault' => false,
                        'missing_key_count' => $languageService->findMissingKeyCount($l->getId()));

                    break;
            }
        }

        $languages = array_merge($active_langs, $inactive_langs);

//try to get additinal langs
        $langsEventParam = new stdClass();
        $langsEventParam->languages = $languages;
        $langsEventParam->inactiveLangs = $inactive_langs;
        $event = new OW_Event('admin.get_additional_langs', array('langs' => $langsEventParam));
        OW::getEventManager()->trigger($event);
        $inactive_langs = $langsEventParam->inactiveLangs;

        $this->assign('langs', $languages);
        $prefixes = $languageService->getPrefixList();

        $this->assign('prefixes', $prefixes);

        $this->assign('active_langs', $active_langs);
        $this->assign('inactive_langs', $inactive_langs);

        $importLangForm = new ImportLangForm();
        $importLangForm->setAction(OW::getRouter()->urlForRoute('admin_settings_language_mod'). "#lang_import");
        $this->addForm($importLangForm);

        $this->addForm(new CloneForm());
    }

    public function activate()
    {
        $tag = $_GET['language'];

        $languageService = BOL_LanguageService::getInstance();

        $language = $languageService->findByTag($tag); /* @var $language BOL_Language */

        $language->setStatus('active');

        $languageService->save($language);
        $url = OW::getRouter()->urlForRoute('admin_settings_language_mod');

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'language_activated'));

        header("location: {$url}#lang_list");
        exit();
    }

    public function deactivate()
    {
        $tag = $_GET['language'];

        $languageService = BOL_LanguageService::getInstance();
        if ( $languageService->countActiveLanguages() == 1 )
        {

            $url = OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'mod');
            header("location: {$url}#lang_list");
            exit();
        }

        $language = $languageService->findByTag($tag); /* @var $language BOL_Language */

        $language->setStatus('inactive');

        $languageService->save($language);

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'language_deactivated'));

        $url = OW::getRouter()->urlForRoute('admin_settings_language_mod');
        header("location: {$url}#lang_list");
        exit();
    }

    public function delete()
    {
        $tag = $_GET['language'];

        $languageService = BOL_LanguageService::getInstance();

        if ( $tag != 'en' ) // don't delete default language
        {
            $language = $languageService->findByTag($tag);

            $languageService->delete($language);
        }

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'language_deleted'));

        $url = OW::getRouter()->urlForRoute('admin_settings_language_mod');
        header("location: {$url}#lang_list");
        exit();
    }

    public function ajaxAddKey()
    {

        $languageService = BOL_LanguageService::getInstance();

        $prefixes = $languageService->getPrefixList();

        $language = $languageService->findById($_POST['language']);

        $addKeyForm = new AddKeyForm($prefixes, $language);

        if ( OW::getRequest()->isPost() && $addKeyForm->isValid($_POST) )
        {
            $data = $addKeyForm->getValues();

            if ( !$this->isDevMode() && !strstr($_SERVER['HTTP_REFERER'], 'dev-tools') )
            {
                $prefixId = $languageService->findPrefixId('ow_custom');
                $key = $languageService->generateCustomKey(trim($data['value']));

                $i = 0;

//$u = $languageService->isKeyUnique( 'ow_custom', $data['key'] );

                $unique = $key;

                while ( !$languageService->isKeyUnique('ow_custom', $unique) )
                {
                    $i++;
                    $unique = $key . $i;
                }

                $key = $unique;
            }
            else
            {
                if ( !$languageService->isKeyUnique($data['prefix'], $data['key']) )
                {
                    exit(json_encode(array('result' => 'dublicate')));
                }

                $prefixId = $languageService->findPrefixId($data['prefix']);

                $key = trim($data['key']);
            }

            $keyDto = new BOL_LanguageKey();

            $languageService->saveKey(
                $keyDto->setKey($key)
                    ->setPrefixId($prefixId)
            );

            $valueDto = new BOL_LanguageValue();

            $valueDto->setKeyId($keyDto->getId())
                ->setLanguageId($language->getId())
                ->setValue($data['value']);

            $languageService->saveValue($valueDto);

            $languageService->generateCache($language->getId());

            OW::getFeedback()->info('Added');
            exit(json_encode(array('result' => 'success')));
        }
    }

    public function ajaxClone()
    {
        $cloneForm = new CloneForm();
        if ( !$cloneForm->isValid($_POST) )
        {
            $errorMessage = OW::getLanguage()->text('admin', 'msg_lang_clone_failed');

            $errors = $cloneForm->getErrors();

            foreach( $errors as $elements )
            {
                foreach( $elements as $error )
                {
                    if ( !empty($error) )
                    {
                        $errorMessage = $error;
                        continue;
                    }
                }
            }

            OW::getFeedback()->error($errorMessage);
            exit(json_encode(array('result' => 'invalid_data')));
        }

        $languageService = BOL_LanguageService::getInstance();
        $data = $cloneForm->getValues();

        $langTag = $data['language'];

        $label = $data['label'];
        $tag = $data['tag'];

        $language = $languageService->findByTag($langTag); /* @var $language BOL_Language */
        $languageService->cloneLanguage($language->getId(), $label, $tag);

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'msg_lang_cloned'));

        exit(json_encode(array('result' => 'success')));
    }

    public function ajaxOrder()
    {
        $languageService = BOL_LanguageService::getInstance();

        $inactiveOrder = 1;
        if ( !empty($_POST['active']) && is_array($_POST['active']) )
        {
            foreach ( $_POST['active'] as $index => $id )
            {
                $dto = $languageService->findById($id); /* @var $dto BOL_Language */

                if ( !empty($dto) )
                {
                    $dto->setStatus('active');
                    $dto->setOrder($index + 1);
                    $languageService->save($dto);

                    $inactiveOrder++;
                }
            }
        }

        if ( !empty($_POST['inactive']) && is_array($_POST['inactive']) )
        {
            foreach ( $_POST['inactive'] as $index => $id )
            {
                $dto = $languageService->findById($id);
                $dto->setStatus('inactive');
                $dto->setOrder($index + $inactiveOrder);
                $languageService->save($dto);
            }
        }



        unset($_COOKIE[BOL_LanguageService::LANG_ID_VAR_NAME]);
        OW::getSession()->delete(BOL_LanguageService::LANG_ID_VAR_NAME);
        $this->service->setCurrentLanguage(BOL_LanguageDao::getInstance()->getCurrent());
        exit;
    }

    public function ajaxEditLangs()
    {

        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        BASE_CMP_LanguageValueEdit::process($_GET['prefix'], $_GET['key']);
    }

    public function ajaxEditLanguageValuesForm()
    {
        $cmp = new BASE_CMP_LanguageValueEdit($_GET['prefix'], $_GET['key']);

        exit(
        json_encode(
            array(
                'markup' => $cmp->render(),
                'js' => OW::getDocument()->getOnloadScript(),
                'include_js' => OW::getDocument()->getScripts()
            )
        )
        );
    }

    public static function isDevMode()
    {
        return $isDevMode = true && strstr(OW::getRequest()->getRequestUri(), 'dev-tools'); // todo: 8aa
    }
}

class ImportLangForm extends Form
{

    public function __construct()
    {
        parent::__construct('import');

        $this->setMethod('post');

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $commandHidden = new HiddenField('command');

        $this->addElement($commandHidden->setValue('upload-lp'));

        $fileField = new FileField('file');
        $fileField->setLabel(OW::getLanguage()->text('admin', 'lang_file'));

        $this->addElement($fileField);

        $submit = new Submit('submit');

        $this->addElement($submit->setValue(OW::getLanguage()->text('admin', 'clone_form_lbl_submit')));
    }
}

class AddKeyForm extends Form
{

    function __construct( $prefixes, $language, $isDevMode = false )
    {
        parent::__construct('form');

        $languageService = BOL_LanguageService::getInstance();

        $this->setAjax(true);

        $this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'ajaxAddKey'));

        $this->setMethod('post');

        $languageHidden = new HiddenField('language');

        $languageHidden->setValue($language->getId());

        $this->addElement($languageHidden);

        $keyTextField = new TextField('key');
        $keyTextField->setLabel(OW::getLanguage()->text('admin', 'add_key_form_lbl_key'));


        $this->addElement(
            $keyTextField->setRequired(ADMIN_CTRL_Languages::isDevMode()));

        $prefixSelectBox = new Selectbox('prefix');

        if ( !empty($_GET['prefix']) && strlen($_GET['prefix']) > 0 )
        {
            $prefixSelectBox->setValue($_GET['prefix']);
        }
        $options = array();

        foreach ( $prefixes as $prefix )
        {
            $options["{$prefix->getPrefix()}"] = $prefix->getLabel();
        }

        $prefixSelectBox->setOptions($options)->setLabel(OW::getLanguage()->text('admin', 'section'));

        $this->addElement(
            $prefixSelectBox->setRequired(ADMIN_CTRL_Languages::isDevMode()));

        $valueTextArea = new Textarea('value');

        $this->addElement(
            $valueTextArea->setRequired(true)->setLabel(OW::getLanguage()->text('admin', 'add_key_form_lbl_val', array('label' => $language->getLabel(), 'tag' => $language->getTag()))));

        $submit = new Submit('submit');

        $submit->setValue(OW::getLanguage()->text('admin', 'add_key_form_lbl_add'));

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->addOnloadScript(
                "owForms['{$this->getName()}'].bind('success', function(json){
				switch( json['result'] ){
					case 'success':
						location.reload();
						break;

					case 'dublicate':
						OW.info('" . OW::getLanguage()->text('admin', 'msg_dublicate_key') . "');
						break;
				}
			});");
        }

        $this->addElement($submit);
    }
}

class CloneForm extends Form
{

    function __construct()
    {
        parent::__construct('clone-form');

        $this->ajax = true;

        $this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'ajaxClone'));

        $this->setMethod('post');
        $labelTextField = new TextField('label');

        $labelTextField->setLabel(OW::getLanguage()->text('admin', 'clone_form_lbl_label'))->setDescription(OW::getLanguage()->text('admin', 'clone_form_descr_label'));

        $this->addElement($labelTextField);

        $tagTextField = new TextField('tag');
        $tagTextField->addValidator(new LanguageTagValidator());
        $tagTextField->setLabel(OW::getLanguage()->text('admin', 'clone_form_lbl_tag'))->setDescription(OW::getLanguage()->text('admin', 'clone_form_descr_tag'));

        $this->addElement($tagTextField);

        $hiddenField = new HiddenField('language');

        $hiddenField->addAttribute('class', 'hidden_lang_tag');

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'clone_form_lbl_submit'));

        $this->addElement($submit);

        $this->addElement($hiddenField); //value to be set by javascript

        if ( !OW::getRequest()->isAjax() )
            OW::getDocument()->addOnloadScript("owForms['{$this->getName()}'].bind('success', function(){location.reload();});");
    }
}

class LanguageTagValidator extends RequiredValidator
{
    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text('admin', 'msg_lang_invalid_language_tag'));
    }

    public function isValid( $value )
    {
        if ( empty($value) )
        {
            return false;
        }

        $languageService = BOL_LanguageService::getInstance();
        $language = $languageService->findByTag($value);

        if ( !empty($language) )
        {
            return false;
        }

        return true;
    }
}