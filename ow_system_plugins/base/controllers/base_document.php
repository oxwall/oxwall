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
class BASE_CTRL_BaseDocument extends OW_ActionController
{

    public function index()
    {
        //TODO implement
    }

    public function alertPage()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
        $this->assign('text', OW::getSession()->get('baseAlertPageMessage'));
        OW::getSession()->delete('baseMessagePageMessage');
    }

    public function confirmPage()
    {
        if ( empty($_GET['back_uri']) )
        {
            throw new Redirect404Exception();
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
        $this->assign('text', OW::getSession()->get('baseConfirmPageMessage'));
        OW::getSession()->delete('baseConfirmPageMessage');
        $this->assign('okBackUrl', OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($_GET['back_uri']), array('confirm-result' => 1)));
        $this->assign('clBackUrl', OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($_GET['back_uri']), array('confirm-result' => 0)));
    }

    public function page404()
    {
        OW::getResponse()->setHeader('HTTP/1.0', '404 Not Found');
        OW::getResponse()->setHeader('Status', '404 Not Found');
        $this->setPageHeading(OW::getLanguage()->text('base', 'base_document_404_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'base_document_404_title'));
        $this->setDocumentKey('base_page404');
    }

    public function page403( array $params )
    {
        $language = OW::getLanguage();
        OW::getResponse()->setHeader('HTTP/1.0', '403 Forbidden');
        OW::getResponse()->setHeader('Status', '403 Forbidden');
        $this->setPageHeading($language->text('base', 'base_document_403_heading'));
        $this->setPageTitle($language->text('base', 'base_document_403_title'));
        $this->setDocumentKey('base_page403');
        $this->assign('message', !empty($params['message']) ? $params['message'] : $language->text('base', 'base_document_403'));
    }

    public function maintenance()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('blank'));
            if ( !empty($_COOKIE['adminToken']) && trim($_COOKIE['adminToken']) == OW::getConfig()->getValue('base', 'admin_cookie') )
            {
                $this->assign('disableMessage', OW::getLanguage()->text('base', 'maintenance_disable_message', array('url' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode('admin/pages/maintenance'))))));
            }
        }
        else
        {
            exit('{}');
        }
    }

    public function splashScreen()
    {
        if ( isset($_GET['agree']) )
        {
            setcookie('splashScreen', 1, time() + 3600 * 24 * 30, '/');
            $url = OW::getRouter()->getBaseUrl();
            $url .= isset($_GET['back_uri']) ? $_GET['back_uri'] : '';
            $this->redirect($url);
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('blank'));
        $this->assign('submit_url', OW::getRequest()->buildUrlQueryString(null, array('agree' => 1)));

        $leaveUrl = OW::getConfig()->getValue('base', 'splash_leave_url');

        if ( !empty($leaveUrl) )
        {
            $this->assign('leaveUrl', $leaveUrl);
        }
    }

    public function passwordProtection()
    {
        $form = new Form('password_protection');
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_BaseDocument', 'passwordProtection'));
        $form->setAjaxDataType(Form::AJAX_DATA_TYPE_SCRIPT);

        $password = new PasswordField('password');
        $form->addElement($password);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base', 'password_protection_submit_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $password = OW::getConfig()->getValue('base', 'guests_can_view_password');
            $data['password'] = crypt($data['password'], OW_PASSWORD_SALT);

            if ( !empty($data['password']) && $data['password'] === $password )
            {
                setcookie('base_password_protection', UTIL_String::getRandomString(), (time() + 86400 * 30), '/');
                echo "OW.info('" . OW::getLanguage()->text('base', 'password_protection_success_message') . "');window.location.reload();";
            }
            else
            {
                echo "OW.error('" . OW::getLanguage()->text('base', 'password_protection_error_message') . "');";
            }
            exit;
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function installCompleted()
    {
        if ( !OW::getRequest()->isAjax() && !empty($_GET['redirect']) )
        {
            if ( !OW::getConfig()->configExists("base", "install_complete") )
            {
                OW::getConfig()->addConfig("base", "install_complete", 1);
            }
            else
            {
                OW::getConfig()->saveConfig("base", "install_complete", 1);
            }

            $this->redirect(OW::getRequest()->buildUrlQueryString(null, array('redirect' => null)));
        }

        $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('blank');
        OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
    }

    public function redirectToMobile()
    {
        $urlToRedirect = OW::getRouter()->getBaseUrl();

        if ( !empty($_GET['back-uri']) )
        {
            $urlToRedirect .= urldecode($_GET['back-uri']);
        }
        
        OW::getApplication()->redirect($urlToRedirect, OW::CONTEXT_MOBILE);
    }

    public function authorizationFailed( array $params )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('base', 'base_document_auth_failed_heading'));
        $this->setPageTitle($language->text('base', 'base_document_auth_failed_heading'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');

        $this->assign('message', !empty($params['message']) ? $params['message'] : null);
    }
//    public function tos()
//    {
//        $language = OW::getLanguage();
//        $this->setPageHeading($language->text('base', 'terms_of_use_page_heading'));
//        $this->setPageTitle($language->text('base', 'terms_of_use_page_heading'));
//        $this->assign('content', $language->text('base', 'terms_of_use_page_content'));
//
//
//        $document = BOL_DocumentDao::getInstance()->findStaticDocument('terms-of-use');
//
//        if ( $document !== null )
//        {
//            $languageService = BOL_LanguageService::getInstance(false);
//            $languageId = $languageService->getCurrent()->getId();
//            $prefix = $languageService->findPrefix('base');
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_heading');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_heading');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_title_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_content');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_content');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_content_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_meta');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_meta');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_meta_tags_{$document->getKey()}"));
//
//            $menuItem = BOL_NavigationService::getInstance()->findMenuItemByDocumentKey($document->getKey());
//
//        }
//    }
}
