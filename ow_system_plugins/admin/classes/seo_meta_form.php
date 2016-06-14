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
 * @package ow_system_plugins.admin.classes
 * @since 1.8.4
 */
class ADMIN_CLASS_SeoMetaForm extends Form
{
    /**
     * Entities
     *
     * @var array
     */
    protected $entities = array();

    /**
     * Get entities
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct('sitemapForm');

        $this->generateEntities();

        $scheduleOptions = array(
            BOL_SeoService::SITEMAP_ENTITY_UPDATE_DAILY => OW::getLanguage()->text('admin', 'seo_sitemap_update_daily'),
            BOL_SeoService::SITEMAP_ENTITY_UPDATE_WEEKLY => OW::getLanguage()->text('admin', 'seo_sitemap_update_weekly'),
            BOL_SeoService::SITEMAP_UPDATE_MONTHLY => OW::getLanguage()->text('admin', 'seo_sitemap_update_monthly'),
        );

        $scheduleField = new Selectbox('schedule');
        $scheduleField->setValue(OW::getConfig()->getValue('base', 'seo_sitemap_schedule_update'));
        $scheduleField->setLabel(OW::getLanguage()->text('admin', 'seo_sitemap_shedule_updates'));
        $scheduleField->setOptions($scheduleOptions);
        $scheduleField->addValidator(new InArrayValidator(array_keys($scheduleOptions)));
        $scheduleField->setRequired(true);

        $this->addElement($scheduleField);

        // submit
        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('base', 'edit_button'));
        $this->addElement($submit);
    }

    /**
     * Generate entities
     *
     * @return void
     */
    protected function generateEntities()
    {
        $entities = OW::getSeoManager()->getSitemapEntities();

        if ( $entities )
        {
            $index = 0;

            foreach ($entities as $entityType => $entityData) {
                $description = !empty($entityData['description'])
                    ? OW::getLanguage()->text($entityData['lang_prefix'], $entityData['description'])
                    : '';

                $entityField = new CheckboxField($entityType);
                $entityField->setLabel(OW::getLanguage()->text($entityData['lang_prefix'], $entityData['label']));
                $entityField->setValue($entityData['enabled']);
                $entityField->setDescription($description);

                $this->addElement($entityField);
                $this->entities[] = $entityField->getName();

                $index++;
            }
        }
    }
}
