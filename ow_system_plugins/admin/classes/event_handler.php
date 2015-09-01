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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CLASS_EventHandler
{
    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetDisableActionList'));
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetJoinStampDisableActionList'), 999);
        $eventManager->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $eventManager->bind('admin.init_floatbox', array($this, 'initFloatbox'));
    }

    public function addAdminNotification( ADMIN_CLASS_NotificationCollector $coll )
    {
        // update soft
        if ( OW::getConfig()->getValue('base', 'update_soft') )
        {
            $coll->add(
                OW::getLanguage()->text('admin', 'notification_soft_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_core_update_request'))),

                ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING
            );
        }

        $pluginsCount = BOL_PluginService::getInstance()->getPluginsToUpdateCount();

        // plugins update
        if ( $pluginsCount > 0 )
        {
            $coll->add(
                OW::getLanguage()->text('admin', 'notification_plugins_to_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_plugins_installed'), 'count' => $pluginsCount)),

                ADMIN_CLASS_NotificationCollector::NOTIFICATION_UPDATE
            );
        }

        $themesCount = BOL_ThemeService::getInstance()->getThemesToUpdateCount();

        // themes update
        if ( $themesCount > 0 )
        {
            $coll->add(
                OW::getLanguage()->text('admin', 'notification_themes_to_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_themes_choose'), 'count' => $themesCount)),

                ADMIN_CLASS_NotificationCollector::NOTIFICATION_UPDATE
            );
        }

        if ( OW::getConfig()->configExists('base', 'cron_is_active') && (int) OW::getConfig()->getValue('base', 'cron_is_active') === 0 )
        {
            $coll->add(
                OW::getLanguage()->text('admin', 'warning_cron_is_not_active', 
                        array('path' => OW_DIR_ROOT . 'ow_cron' . DS . 'run.php')),

                ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING
            );
        }

        if ( !ini_get('allow_url_fopen') )
        {
            $coll->add(
                OW::getLanguage()->text('admin', 'warning_url_fopen_disabled'),
                ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING
            );
        }
    }

    public function onGetDisableActionList( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name != 'joinStamp' )
        {
            $dto = $params['questionDto'];

            foreach ( $data as $key => $value )
            {
                switch($key)
                {
                    case 'disable_account_type' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_account_type'] = true;
                        }
                        
                        break;
                    case 'disable_answer_type' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_answer_type'] = true;
                        }
                        
                        break;
                    case 'disable_presentation' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_presentation'] = true;
                        }
                        
                        break;
                    case 'disable_column_count' :
                                                
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_column_count'] = true;
                        }
                        
                        break;
                        
                    case 'disable_possible_values' :
                        
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_possible_values'] = true;
                        }
                        
                        break;
                    
                    case 'disable_display_config' :

                        if ( $dto->name == 'joinStamp' )
                        {
                            $data['disable_display_config'] = true;
                        }

                        break;
                    case 'disable_required' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_required'] = true;
                        }

                        
                        break;
                    case 'disable_on_join' :

                        if ( in_array($dto->name, array('password') ) || $dto->base == 1 )
                        {
                            $data['disable_on_join'] = true;
                        }

                        break;
                    case 'disable_on_view' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_view'] = true;
                        }
                        break;
                    case 'disable_on_search' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_search'] = true;
                        }
                        break;
                    case 'disable_on_edit' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_edit'] = true;
                        }
                        break;
                }
            }
        }

        $e->setData($data);
    }
    
    function onGetJoinStampDisableActionList( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'joinStamp' )
        {
            $disableActionList = array(
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => true,
                'disable_display_config' => true,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => true,
                'disable_on_edit' => true
            );

            $e->setData($disableActionList);
        }
    }

    public function initFloatbox( OW_Event $event )
    {
        static $isInitialized = false;

        if ( $isInitialized )
        {
            return;
        }

        $params = $event->getParams();
        $layout = (!empty($params['layout']) && in_array($params['layout'], array('page', 'floatbox'))) ? $params['layout'] : 'floatbox';

        $document = OW::getDocument();
        $basePlugin = OW::getPluginManager()->getPlugin('base');

        $document->addStyleSheet($basePlugin->getStaticCssUrl() . 'photo_floatbox.css');
        $document->addScript($basePlugin->getStaticJsUrl() . 'jquery-ui.min.js');
        $document->addScript($basePlugin->getStaticJsUrl() . 'slider.min.js', 'text/javascript', 1000000);
        $document->addScript($basePlugin->getStaticJsUrl() . 'photo.js');

        $language = OW::getLanguage();

        $language->addKeyForJs('admin', 'tb_edit_photo');
        $language->addKeyForJs('admin', 'confirm_delete');
        $language->addKeyForJs('admin', 'mark_featured');
        $language->addKeyForJs('admin', 'remove_from_featured');
        $language->addKeyForJs('admin', 'rating_total');
        $language->addKeyForJs('admin', 'rating_your');
        $language->addKeyForJs('admin', 'of');
        $language->addKeyForJs('admin', 'album');
        $language->addKeyForJs('base', 'rate_cmp_owner_cant_rate_error_message');
        $language->addKeyForJs('base', 'rate_cmp_auth_error_message');
        $language->addKeyForJs('admin', 'slideshow_interval');
        $language->addKeyForJs('admin', 'pending_approval');

        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString('
                ;window.photoViewParams = Object.defineProperties({}, {
                    ajaxResponder:{value: {$ajaxResponder}, enumerable: true},
                    rateUserId: {value: {$rateUserId}, enumerable: true},
                    layout: {value: {$layout}, enumerable: true},
                    isClassic: {value: {$isClassic}, enumerable: true},
                    urlHome: {value: {$urlHome}, enumerable: true},
                    isDisabled: {value: {$isDisabled}, enumerable: true},
                    isEnableFullscreen: {value: {$isEnableFullscreen}, enumerable: true}
                });',
                array(
                    'ajaxResponder' => OW::getRouter()->urlFor('ADMIN_CTRL_Theme', 'ajaxResponder'),
                    'rateUserId' => OW::getUser()->getId(),
                    'layout' => $layout,
                    'isClassic' => (bool)OW::getConfig()->getValue('photo', 'photo_view_classic'),
                    'urlHome' => OW_URL_HOME,
                    'isDisabled' => false,
                    'isEnableFullscreen' => (bool)OW::getConfig()->getValue('photo', 'store_fullsize')
                )
            )
        );

        $document->addOnloadScript(';window.photoView.init();');

        $cmp = new ADMIN_CMP_UploadedFilesFloatbox($layout);
        $document->appendBody($cmp->render());

        $isInitialized = true;
    }
}
