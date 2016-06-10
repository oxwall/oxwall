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
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.8.4
 */
class ADMIN_CTRL_Seo extends ADMIN_CTRL_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // activate menu item
        OW::getDocument()->getMasterPage()->
                getMenu(OW_Navigation::ADMIN_SETTINGS)->getElement('sidebar_menu_item_seo_settings')->setActive(true);

        $this->setPageHeading(OW::getLanguage()->text('admin', 'seo_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_edit');

        // register components
        $this->addComponent('menu', $this->getMenu());
    }

    /**
     * Index
     */
    public function index()
    {
    }

    /**
     * Sitemap
     */
    public function sitemap()
    {
        $form = new ADMIN_CLASS_SeoSitemapForm();
        $this->addForm($form);

        // validate and save config
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $entities = OW::getSeoManager()->getSitemapEntities();
            $formValues = $form->getValues();

            // save entities status
            foreach ( $entities as $entity => $entityData )
            {
                $formValues[$entity]
                    ? OW::getSeoManager()->enableSitemapEntity($entity)
                    : OW::getSeoManager()->disableSitemapEntity($entity);
            }

            // save schedule
            OW::getConfig()->saveConfig('base', 'seo_sitemap_schedule_update', $formValues['schedule']);

            // reload the current page
            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'seo_sitemap_settings_updated'));
            $this->redirect();
        }

        // assign view variables
        $this->assign('formEntitites', $form->getEntities());
        $this->assign('sitemapUrl', OW::getSeoManager()->getSitemapUrl());
    }

    /**
     * Social meta
     */
    public function socialMeta()
    {
    }

    /**
     * Get menu
     *
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $items = array();

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_page'));
        $item->setIconClass('ow_ic_files');
        $item->setKey('seo_page');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo'));
        $item->setOrder(1);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_sitemap'));
        $item->setIconClass('ow_ic_script');
        $item->setKey('seo_sitemap');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo_sitemap'));
        $item->setOrder(2);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_social_meta'));
        $item->setIconClass('ow_ic_flag');
        $item->setKey('seo_social_meta');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo_social_meta'));
        $item->setOrder(3);
        $items[] = $item;

        return new BASE_CMP_ContentMenu($items);
    }
}

