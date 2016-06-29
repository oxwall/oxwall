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
 * @author Aybat Duyshokov <duyshokov@gmail.com>, Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_Pages extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_files');
    }

    public function index( $params )
    {
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_PAGES)->getElement('sidebar_menu_item_pages_manage')->setActive(true);

        $service = BOL_NavigationService::getInstance();

        $menuItem = empty($params['menu']) ? null : $service->findMenuItemById($params['menu']);

        $form = new SaveForm($this, $menuItem);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process($params);
        }
    }

    public function manage()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

        $service = BOL_NavigationService::getInstance();

        function compare( $item, $item2 )
        {
            return $item['order'] > $item2['order'];
        }

        $mainMenuItems = $service->findMenuItems(BOL_NavigationService::MENU_TYPE_MAIN);
        usort($mainMenuItems, 'compare');
        $bottomMenuItems = $service->findMenuItems(BOL_NavigationService::MENU_TYPE_BOTTOM);
        usort($bottomMenuItems, 'compare');
        $hiddenMenuItems = $service->findMenuItems(BOL_NavigationService::MENU_TYPE_HIDDEN);
        usort($hiddenMenuItems, 'compare');

        $menuItems = array( 'main' => $mainMenuItems,
            'bottom' => $bottomMenuItems,
            'hidden' => $hiddenMenuItems );

        $this->assign('menuItems', $menuItems);
    }

    public function ajaxReorder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $service = BOL_NavigationService::getInstance();

        if ( !empty($_POST['main-menu']) )
        {
            foreach ( $_POST['main-menu'] as $order => $id )
            {
                $dto = $service->findMenuItemById($id);
                if ( empty($dto) )
                    continue;

                $dto->setType(BOL_NavigationService::MENU_TYPE_MAIN)->setOrder($order + 1);
                $service->saveMenuItem($dto);
            }
        }

        if ( !empty($_POST['bottom-menu']) )
        {
            foreach ( $_POST['bottom-menu'] as $order => $id )
            {
                $dto = $service->findMenuItemById($id);
                $dto->setType(BOL_NavigationService::MENU_TYPE_BOTTOM)->setOrder($order + 1);
                $service->saveMenuItem($dto);
            }
        }

        if ( !empty($_POST['hidden-menu']) )
        {
            foreach ( $_POST['hidden-menu'] as $order => $id )
            {
                $dto = $service->findMenuItemById($id);
                $dto->setType(BOL_NavigationService::MENU_TYPE_HIDDEN)->setOrder($order + 1);
                $service->saveMenuItem($dto);
            }
        }

        exit();
    }

    public function maintenance()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('admin', 'maintenance_page_heading'));
        $this->setPageTitle($language->text('admin', 'maintenance_page_title'));

        $form = new Form('maintenance');

        $maintananceEnable = new CheckboxField('maintenance_enable');
        $maintananceEnable->setLabel($language->text('admin', 'maintenance_enable_label'));
        $maintananceEnable->setDescription($language->text('admin', 'maintenance_enable_desc'));
        $form->addElement($maintananceEnable);

        $intro = new Textarea('maintenance_text');
        $intro->setLabel($language->text('admin', 'maintenance_text_label'));
        $intro->setDescription($language->text('admin', 'maintenance_text_desc'));
        $form->addElement($intro);

        // splash screen
        $splashScreenEnable = new CheckboxField('splash_screen');
        $splashScreenEnable->setLabel($language->text('admin', 'splash_enable_label'));
        $splashScreenEnable->setDescription($language->text('admin', 'splash_enable_desc'));
        $form->addElement($splashScreenEnable);

        $intro = new Textarea('intro');
        $intro->setLabel($language->text('admin', 'splash_intro_label'));
        $intro->setDescription($language->text('admin', 'splash_intro_desc'));
        $form->addElement($intro);

        $buttonLabel = new TextField('button_label');
        $buttonLabel->setLabel($language->text('admin', 'splash_button_label'));
        $buttonLabel->setDescription($language->text('admin', 'splash_button_label_desc'));
        $form->addElement($buttonLabel);

        $leaveUrl = new TextField('leave_url');
        $leaveUrl->setLabel($language->text('admin', 'splash_leave_url_label'));
        $leaveUrl->setDescription($language->text('admin', 'splash_leave_url_desc'));
        $leaveUrl->addValidator(new UrlValidator());
        $form->addElement($leaveUrl);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'permissions_index_save'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $langService = BOL_LanguageService::getInstance();

                $key = $langService->findKey('admin', 'maintenance_text_value');

                if ( $key === null )
                {
                    $prefix = $langService->findPrefix('admin');
                    $key = new BOL_LanguageKey();
                    $key->setKey('maintenance_text_value');
                    $key->setPrefixId($prefix->getId());
                    $langService->saveKey($key);
                }

                $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

                if ( $value === null )
                {
                    $value = new BOL_LanguageValue();
                    $value->setKeyId($key->getId());
                    $value->setLanguageId($langService->getCurrent()->getId());
                }

                $value->setValue($data['maintenance_text']);
                $langService->saveValue($value);


                OW::getConfig()->saveConfig('base', 'maintenance', (bool) $data['maintenance_enable']);

                // save splash screen
                $key = $langService->findKey('admin', 'splash_intro_value');

                if ( $key === null )
                {
                    $prefix = $langService->findPrefix('admin');
                    $key = new BOL_LanguageKey();
                    $key->setKey('splash_intro_value');
                    $key->setPrefixId($prefix->getId());
                    $langService->saveKey($key);
                }

                
                $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

                if ( $value === null )
                {
                    $value = new BOL_LanguageValue();
                    $value->setKeyId($key->getId());
                    $value->setLanguageId($langService->getCurrent()->getId());
                }

                $value->setValue($data['intro']);
                $langService->saveValue($value);

                $key = $langService->findKey('admin', 'splash_button_value');

                if ( $key === null )
                {
                    $prefix = $langService->findPrefix('admin');
                    $key = new BOL_LanguageKey();
                    $key->setKey('splash_button_value');
                    $key->setPrefixId($prefix->getId());
                    $langService->saveKey($key);
                }

                $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

                if ( $value === null )
                {
                    $value = new BOL_LanguageValue();
                    $value->setKeyId($key->getId());
                    $value->setLanguageId($langService->getCurrent()->getId());
                }

                $value->setValue($data['button_label']);
                $langService->saveValue($value);

                $url = trim($data['leave_url']);

                if ( !empty($url) && !strstr($url, 'http') )
                {
                    $url = 'http://' . $url;
                }

                OW::getConfig()->saveConfig('base', 'splash_leave_url', $url);
                OW::getConfig()->saveConfig('base', 'splash_screen', (bool) $data['splash_screen']);

                OW::getFeedback()->info($language->text('admin', 'maintenance_submit_success_message'));
                $this->redirect();
            }
        }

        $form->getElement('maintenance_text')->setValue($language->text('admin', 'maintenance_text_value'));
        $form->getElement('maintenance_enable')->setValue((bool) OW::getConfig()->getValue('base', 'maintenance'));

        // splash screen
        $form->getElement('intro')->setValue($language->text('admin', 'splash_intro_value'));
        $form->getElement('button_label')->setValue($language->text('admin', 'splash_button_value'));
        $form->getElement('leave_url')->setValue(OW::getConfig()->getValue('base', 'splash_leave_url'));
        $form->getElement('splash_screen')->setValue((bool) OW::getConfig()->getValue('base', 'splash_screen'));
    }

}

class SaveForm extends Form
{

    public function __construct( OW_Renderable $rendrable )
    {
        parent::__construct('page-add-form');

        $language = OW::getLanguage();

        $titleTextField = new TextField('title');

        $titleTextField->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_page_title'))
                ->addAttribute('class', 'ow_text');

        $titleTextField->setId('title');

        $isLocal = true;

        if ( OW::getRequest()->isPost() )
        {
            $isLocal = ($_POST['type'] == 'local') ? true : false;
        }

        $titleTextField->addValidator(new PageTitleValidator());

        $this->addElement($titleTextField);

        $nameTextField = new TextField('name');

        $nameTextField->setLabel(OW::getLanguage()->text('admin', 'pages_add_menu_name'))
                ->setRequired(true)
                ->addAttribute('class', 'ow_text');

        $this->addElement($nameTextField);

        $visibleForCheckboxGroup = new CheckboxGroup('visible-for');

        $opts = array(
            '1' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
            '2' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_members')
        );

        $visibleForCheckboxGroup->setOptions($opts);
        $visibleForCheckboxGroup->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_visible_for'));

        $this->addElement($visibleForCheckboxGroup);

//        $metaTagsTextarea = new Textarea('meta-tags');
//        $metaTagsTextarea->setLabel('Page meta tags')
//                ->setId('meta-tags')
//                ->setDescription(OW::getLanguage()->text('admin', 'pages_page_field_meta_desc'));

        $keywords = new Textarea("meta_keywords");
        $keywords->setLabel($language->text("base", "pages_page_meta_keywords_label"));
        $keywords->setDescription($language->text("base", "pages_page_meta_keywords_desc"));
        $this->addElement($keywords);

        $desc = new Textarea("meta_desc");
        $desc->setLabel($language->text("base", "pages_page_meta_desc_label"));
        $desc->setDescription($language->text("base", "pages_page_meta_desc_desc"));
        $this->addElement($desc);

        $metaTagsTextarea = new Textarea('meta-tags');
        $metaTagsTextarea->setLabel('Page meta tags')
            ->setId('meta-tags')
            ->setDescription(OW::getLanguage()->text('admin', 'pages_page_field_meta_desc'));


        $this->addElement($metaTagsTextarea);

        $contentTextArea = new Textarea('content');

        $contentTextArea->setLabel(OW::getLanguage()->text('admin', 'pages_add_page_content'))
                ->setId('content')
                ->setDescription(
                        OW::getLanguage()->text('admin', 'pages_page_field_content_desc', array(
                            'src' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'question.png',
                            'url' => '#'
                                )
                        )
        );

        $this->addElement($contentTextArea);

        $typeHiddenField = new TextField('type');

        $type = (OW::getRequest()->isPost() && $_POST['type']) ? $_POST['type'] : 'local';

        $rendrable->assign('isLocal', $isLocal);

        $typeHiddenField->setValue($type);
        $typeHiddenField->setId('type');

        $typeHiddenField->setLabel(OW::getLanguage()->text('admin', 'page_add_page_address'));

        $this->addElement($typeHiddenField);

        $localUrlTextField = new TextField('local-url');
        $localUrlTextField->addValidator(new LocalPageUrlValidator())->addValidator(new LocalPageUniqueValidator());

        $localUrlTextField->setId('url1');

        $this->addElement($localUrlTextField);

        $externalUrl = new TextField('external-url');
        $externalUrl->setInvitation('http://www.example.com')->setHasInvitation(true)->
                addValidator(new ADMIN_CLASS_ExternalPageUrlValidator())->setId('url2');

        $this->addElement($externalUrl);

        $extOpenInNewWindow = new CheckboxField('ext-open-in-new-window');
        $extOpenInNewWindow->setLabel(OW::getLanguage()->text('admin', 'pages_edit_external_url_open_in_new_window'));

        $this->addElement($extOpenInNewWindow);

        $submit = new Submit('submit');

        $this->addElement($submit->setValue(OW::getLanguage()->text('base', 'pages_add_submit')));
    }

    public function process( $params )
    {

        $service = BOL_NavigationService::getInstance();
        /* @var $service BOL_NavigationService */

        $menuItem = new BOL_MenuItem();
        $doc_key = UTIL_HtmlTag::generateAutoId('page');
        $menuItem->setDocumentKey($doc_key);
        $menuItem->setPrefix('base');
        $menuItem->setKey($doc_key);

        $menuItem->setType($params['type']);

        $order = $service->findMaxSortOrderForMenuType($params['type']);
        $order;
        $menuItem->setOrder($order);

        $visibleFor = 0;

        $arr = !empty($_POST['visible-for']) ? $_POST['visible-for'] : array( );

        foreach ( $arr as $val )
        {
            $visibleFor += $val;
        }

        //hotfix
        if ( $visibleFor === 0 )
        {
            $visibleFor = 3;
        }

        $menuItem->setVisibleFor($visibleFor);

        $url = '';

        $languageService = BOL_LanguageService::getInstance();

        $prefixDto = $languageService->findPrefix($menuItem->getPrefix());

        switch ( $_POST['type'] )
        {
            case 'local' :

                $service->saveMenuItem($menuItem);

                $document = new BOL_Document();
                $document->setIsStatic(true);
                $document->setKey($menuItem->getKey());

                $url = str_replace(UTIL_String::removeFirstAndLastSlashes(OW::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($_POST['local-url']));
                $document->setUri(UTIL_String::removeFirstAndLastSlashes($url));

                $service->saveDocument($document);

//- name

                $currentLanguageId = $languageService->getCurrent()->getId();

                $keyDto = $languageService->addKey($prefixDto->getId(), $menuItem->getKey());

                $menuName = $_POST['name'];
                $languageService->addValue($currentLanguageId, $menuItem->getPrefix(), $keyDto->getKey(), $menuName);

//- title

                $keyDto = $languageService->addKey($prefixDto->getId(), 'local_page_title_' . $menuItem->getKey());

                $title = ( empty($_POST['title']) ) ? '' : $_POST['title'];
                $languageService->addValue($currentLanguageId, $menuItem->getPrefix(), $keyDto->getKey(), $title);

//-	meta tags
                $keyDto = $languageService->addKey($prefixDto->getId(), 'local_page_meta_desc_' . $menuItem->getKey());
                $metaTagsStr = ( empty($_POST['meta_desc']) ) ? '' : $_POST['meta_desc'];
                $languageService->addValue($currentLanguageId, $menuItem->getPrefix(), $keyDto->getKey(), $metaTagsStr);

                $keyDto = $languageService->addKey($prefixDto->getId(), 'local_page_meta_keywords_' . $menuItem->getKey());
                $metaTagsStr = ( empty($_POST['meta_keywords']) ) ? '' : $_POST['meta_keywords'];
                $languageService->addValue($currentLanguageId, $menuItem->getPrefix(), $keyDto->getKey(), $metaTagsStr);
//- content
                $keyDto = $languageService->addKey($prefixDto->getId(), 'local_page_content_' . $menuItem->getKey());

                $contentStr = ( empty($_POST['content']) ) ? '' : $_POST['content'];
                $languageService->addValue($currentLanguageId, $menuItem->getPrefix(), $keyDto->getKey(), $contentStr);
//~

                $languageService->generateCache($currentLanguageId);

                break;

            case 'external' :

                $menuItem->setExternalUrl($_POST['external-url']);

                $menuItem->setNewWindow((!empty($_POST['ext-open-in-new-window']) && $_POST['ext-open-in-new-window'] == 'on' ) ? true : false);

                $service->saveMenuItem($menuItem);

                $keyDto = $languageService->addKey($prefixDto->getId(), $menuItem->getKey());

                $languageService->addValue($languageService->getCurrent()->getId(), $menuItem->getPrefix(), $keyDto->getKey(), $_POST['name']);

                $languageService->generateCache($languageService->getCurrent()->getId());

                break;
        }

        header('location: ' . OW::getRouter()->urlForRoute('admin_pages_main'));
        exit();
    }

}

class PageTitleValidator extends OW_Validator
{

    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text('base', 'form_validator_required_error_message'));
    }

    public function isValid( $value )
    {
        if ( !empty($_POST['external-url']) )
            return true;

        return !( empty($_POST['local-url']) || empty($value) );
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage( $errorMessage )
    {
        if ( $errorMessage === null || mb_strlen(trim($errorMessage)) === 0 )
        {
            throw new InvalidArgumentException('Invalid error message!');
        }

        $this->errorMessage = trim($errorMessage);
    }

    function getJsValidator()
    {

        return "{
        	validate : function( value ){
                if( $('#address1').attr('checked') &&  !value ){ throw " . json_encode($this->getError()) . "; }
                return true;
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }

}

class LocalPageUniqueValidator extends OW_Validator
{

    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text('base', 'unique_local_page_error'));
    }

    public function isValid( $value )
    {
        if ( !empty($_POST['external-url']) )
            return true;

        return BOL_NavigationService::getInstance()->isDocumentUriUnique($value);
    }

}

class LocalPageUrlValidator extends OW_Validator
{

    private $errCode;

    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text('base', 'form_validator_required_error_message'));
    }

    public function isValid( $value )
    {
        if ( !empty($_POST['external-url']) )
        {
            return true;
        }

        $value = str_replace(UTIL_String::removeFirstAndLastSlashes(OW::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($value));

        $bool = !( empty($_POST['local-url']) || empty($value) );

        $this->errCode = $bool == false ? 1 : 0;

        if ( $bool )
        {
            if ( !preg_match('/^[\w\-.]+[\/\w\-.]+$/', trim($value)) )
            {
                $this->errCode = 2;
                return false;
            }
        }

        return $bool;
    }

    public function getError()
    {
        if ( $this->errCode == 2 )
        {
            return OW::getLanguage()->text('base', 'pages_wrong_local_url');
        }

        return $this->errorMessage;
    }

    public function setErrorMessage( $errorMessage )
    {
        if ( $errorMessage === null || mb_strlen(trim($errorMessage)) === 0 )
        {
            throw new InvalidArgumentException('Invalid error message!');
        }

        $this->errorMessage = trim($errorMessage);
    }

    function getJsValidator()
    {

        return "{
        	validate : function( value ){
                if( $('#address1').attr('checked') &&  !value ){
                	throw " . json_encode($this->getError()) . ";
    			}

                return true;
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }

}

class ExternalPageUrlValidator extends UrlValidator
{

    public function __construct()
    {
        parent::__construct();
    }

    public function isValid( $value )
    {
        if ( !empty($_POST['local-url']) && empty($_POST['external-url']) )
        {
            return true;
        }

        if ( !empty($_POST['external-url']) )
        {
            return parent::isValid($_POST['external-url']);
        }

        return false;
    }

    public function getJsValidator()
    {

        return "{
        	validate : function( value ){
                if( $('#address2').attr('checked') &&  !value ){
                	throw " . json_encode(OW::getLanguage()->text('base', 'form_validator_required_error_message')) . ";
    			}

                return true;
        },
        	getErrorMessage : function(){ return " . json_encode(OW::getLanguage()->text('base', 'form_validator_required_error_message')) . " }
        }";
    }

}

?>
