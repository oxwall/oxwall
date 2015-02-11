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
class ADMIN_CTRL_PagesEditExternal extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_PAGES)->getElement('sidebar_menu_item_pages_manage')->setActive(true);
    }

    public function delete( $params )
    {

        $id = (int) $params['id'];

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $service = BOL_NavigationService::getInstance();

        $languageService = BOL_LanguageService::getInstance();

        $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());

        if ( !empty($langKey) )
        {
            $list = $languageService->findAll();

            foreach ( $list as $dto )
            {
                $langValue = $languageService->findValue($dto->getId(), $langKey->getId());

                if ( empty($langValue) )
                {
                    continue;
                }

                $languageService->deleteValue($langValue);
            }

            $languageService->deleteKey($langKey->getId());
        }

        $service->deleteMenuItem($menu);

        $this->redirect(OW::getRouter()->urlForRoute('admin_pages_main'));
    }

    public function index( $params )
    {

        $id = (int) $params['id'];

        $this->assign('id', $id);

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $service = BOL_NavigationService::getInstance();

        $form = new EditExternalPageForm('edit-form', $menu);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            $visibleFor = 0;
            $arr = !empty($data['visible-for']) ? $data['visible-for'] : array();
            foreach ( $arr as $val )
            {
                $visibleFor += $val;
            }

            $service->saveMenuItem(
                    $menu->setExternalUrl($data['url'])
                    ->setVisibleFor($visibleFor)
                    ->setNewWindow((!empty($_POST['ext-open-in-new-window']) && $_POST['ext-open-in-new-window'] == 'on'))
            );

            $languageService = BOL_LanguageService::getInstance();

            $plugin = OW::getPluginManager()->getPlugin('base');

            $langKey = $languageService->findKey($plugin->getKey(), $menu->getKey());

            if ( empty($langKey) )
            {
                $langPrefixDto = $languageService->findPrefix($menu->getPrefix());
                $langKey = $languageService->addKey($langPrefixDto->getId(), $menu->getKey());
            }

            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            if ( empty($langValue) )
            {
                $languageService->addValue($languageService->getCurrent()->getId(), $menu->getPrefix(), $langKey->getKey(), $data['name']);
            }
            else
            {
                $languageService->saveValue(
                    $langValue->setValue($data['name'])
                );
            }

            $adminPlugin = OW::getPluginManager()->getPlugin('admin');

            OW::getFeedback()->info(OW::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();
        }

        $this->addForm($form);
    }
}

class EditExternalPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $language = OW_Language::getInstance();

        $plugin = OW::getPluginManager()->getPlugin('base');
        $adminPlugin = OW::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $this->addElement(
                $nameTextField->setValue($language->text($plugin->getKey(), $menu->getKey()))
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_external_menu_name_label'))
                ->setRequired(true)
        );

        $urlTextField = new TextField('url');

        $urlTextField->addValidator( new ADMIN_CLASS_ExternalPageUrlValidator() );

        $this->addElement(
                $urlTextField->setValue($menu->getExternalUrl())
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_external_url_label'))
                ->setRequired(true)
        );

        $extOpenInNewWindow = new CheckboxField('ext-open-in-new-window');

        $this->addElement(
                $extOpenInNewWindow->setLabel(OW::getLanguage()->text('admin', 'pages_edit_external_url_open_in_new_window'))
                ->setValue($menu->getNewWindow())
        );

        $visibleForCheckboxGroup = new CheckboxGroup('visible-for');

        $visibleFor = $menu->getVisibleFor();

        $options = array(
            '1' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
            '2' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_members')
        );

        $values = array();

        foreach ( $options as $value => $option )
        {
            if ( !($value & $visibleFor) )
                continue;

            $values[] = $value;
        }

        $this->addElement(
                $visibleForCheckboxGroup->setOptions($options)
                ->setValue($values)
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_external_visible_for'))
        );



        $saveSubmit = new Submit('save');

        $this->addElement(
            $saveSubmit->setValue($language->text($adminPlugin->getKey(), 'save_btn_label'))
        );
    }
}