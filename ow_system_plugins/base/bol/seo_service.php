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
 * Seo service.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.8.4
 */
class BOL_SeoService
{
    /**
     * Sitemap entity update weekly
     */
    const SITEMAP_ENTITY_UPDATE_WEEKLY = 'weekly';

    /**
     * Sitemap entity update daily
     */
    const SITEMAP_ENTITY_UPDATE_DAILY = 'daily';

    /**
     * Sitemap file name
     */
    const SITEMAP_FILE_NAME = 'sitemap.xml';

    /**
     * Sitemap dir name
     */
    const SITEMAP_DIR_NAME = 'sitemap';

    /**
     * Sitemap update daily
     */
    const SITEMAP_UPDATE_DAILY = 'daily';

    /**
     * Sitemap update weekly
     */
    const SITEMAP_UPDATE_WEEKLY = 'weekly';

    /**
     * Sitemap update monthly
     */
    const SITEMAP_UPDATE_MONTHLY = 'monthly';

    /**
     * Singleton instance.
     *
     * @var BOL_SeoService
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    private function __construct()
    {}

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SeoService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Get sitemap url
     *
     * @return string
     */
    public function getSitemapUrl()
    {
        return OW::getRouter()->urlForRoute('base.sitemap');
    }

    /**
     * Get sitemap path
     *
     * @return string
     */
    public function getSitemapPath()
    {
        return $this->getBaseSitemapPath() . self::SITEMAP_FILE_NAME;
    }

    /**
     * Get base sitemap path
     *
     * @return string
     */
    public function getBaseSitemapPath()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . self::SITEMAP_DIR_NAME . '/';
    }

    /**
     * Get sitemap entities
     *
     * @return array
     */
    public function getSitemapEntities()
    {
        return json_decode(OW::getConfig()->getValue('base', 'seo_sitemap_entities'), true);
    }

    /**
     * Add sitemap entity
     *
     * @param string $langPrefix
     * @param string $label
     * @param string $entityType
     * @param string $description
     * @param array $entityItems
     * @param float $priority
     * @param string $changeFreq
     * @return void
     */
    public function addSitemapEntity($langPrefix, $label, $entityType, array $entityItems, $description = null, $priority = 0.5, $changeFreq = self::SITEMAP_ENTITY_UPDATE_WEEKLY)
    {
        $entities = $this->getSitemapEntities();

        if ( !array_key_exists($entityType, $entities) )
        {
            // process entity items
            $processedEntityItems = array();
            foreach ($entityItems as $item) {
                $processedEntityItems[] = array(
                    'name' => $item,
                    'data_fetched' => false,
                    'offset' => null
                );
            }

            $entities[$entityType] = array(
                'lang_prefix' => $langPrefix,
                'label' => $label,
                'description' => $description,
                'entities' => $processedEntityItems,
                'enabled' => true,
                'priority' => $priority,
                'changefreq' => $changeFreq
            );

            OW::getConfig()->saveConfig('base', 'seo_sitemap_entities', json_encode($entities));
        }
    }

    /**
     * Enable sitemap entity
     *
     * @param string $entityType
     * @return void
     */
    public function enableSitemapEntity($entityType)
    {
        $this->setSitemapEntityStatus($entityType);
    }

    /**
     * Disable sitemap entity
     *
     * @param string $entityType
     * @return void
     */
    public function disableSitemapEntity($entityType)
    {
        $this->setSitemapEntityStatus($entityType, false);
    }

    /**
     * Remove sitemap entity
     *
     * @param string $entityType
     * @return void
     */
    public function removeSitemapEntity($entityType)
    {
        $entities = $this->getSitemapEntities();

        if ( array_key_exists($entityType, $entities) )
        {
            unset($entities[$entities]);

            OW::getConfig()->saveConfig('base', 'seo_sitemap_entities', json_encode($entities));
        }
    }

    /**
     * Set sitemap entity status
     *
     * @param string $entityType
     * @param boolean $enabled
     * @return void
     */
    protected function setSitemapEntityStatus($entityType, $enabled = true)
    {
        $entities = $this->getSitemapEntities();

        if ( array_key_exists($entityType, $entities) )
        {
            $entities[$entityType]['enabled'] = $enabled;

            OW::getConfig()->saveConfig('base', 'seo_sitemap_entities', json_encode($entities));
        }
    }
}
