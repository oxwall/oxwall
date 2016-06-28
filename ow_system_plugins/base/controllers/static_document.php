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
class BASE_CTRL_StaticDocument extends OW_ActionController
{
    /**
     * @var BOL_NavigationService
     */
    private $navService;

    public function __construct()
    {
        parent::__construct();
        $this->navService = BOL_NavigationService::getInstance();
    }

    public function index( $params )
    {
        if ( empty($params['documentKey']) )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        $documentKey = $params['documentKey'];

        $document = $this->navService->findDocumentByKey($documentKey);

        if ( $document === null )
        {
            throw new Redirect404Exception();
        }

        $menuItem = $this->navService->findMenuItemByDocumentKey($document->getKey());

        if ( $menuItem !== null )
        {
            if ( !$menuItem->getVisibleFor() || ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_GUEST && OW::getUser()->isAuthenticated() ) )
            {
                throw new Redirect403Exception();
            }

            if ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_MEMBER && !OW::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }
        }

        $this->assign('content', $language->text('base', "local_page_content_{$document->getKey()}"));
        $this->setPageHeading($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->setPageTitle($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->documentKey = $document->getKey();

        $this->setDocumentKey($document->getKey());

        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'setCustomMetaInfo'));
    }

    public function setCustomMetaInfo()
    {
        OW::getDocument()->setDescription(null);

        if ( OW::getLanguage()->valueExist('base', "local_page_meta_desc_{$this->getDocumentKey()}") )
        {
            OW::getDocument()->setDescription(OW::getLanguage()->text('base', "local_page_meta_desc_{$this->getDocumentKey()}"));
        }

        if ( OW::getLanguage()->valueExist('base', "local_page_meta_keywords_{$this->getDocumentKey()}") )
        {
            OW::getDocument()->setKeywords(OW::getLanguage()->text('base', "local_page_meta_keywords_{$this->getDocumentKey()}"));
        }
    }
}
