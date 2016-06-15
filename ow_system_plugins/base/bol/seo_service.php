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
     * Sitemap max urls per file
     */
    const SITEMAP_MAX_URLS_IN_FILE = 50000;

    /**
     * Sitemap item update weekly
     */
    const SITEMAP_ITEM_UPDATE_WEEKLY = 'weekly';

    /**
     * Sitemap item update daily
     */
    const SITEMAP_ITEM_UPDATE_DAILY = 'daily';

    /**
     * Sitemap file name
     */
    const SITEMAP_FILE_NAME = 'sitemap%s.xml.gz';

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
     * Sitemap
     *
     * @var BOL_SitemapDao
     */
    protected $sitemapDao;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->sitemapDao = BOL_SitemapDao::getInstance();
    }

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
     * @param integer $part
     * @return string
     */
    public function getSitemapUrl($part = null)
    {
        $url =  OW::getRouter()->urlForRoute('base.sitemap');

        return $part
            ? $url . '?part=' . $part
            : $url;
    }

    /**
     * Get sitemap path
     *
     * @param integer $part
     * @return string
     */
    public function getSitemapPath($part = null)
    {
        $sitemapBuild = (int) OW::getConfig()->getValue('base', 'seo_sitemap_last_build');
        $sitemapPath = $this->getBaseSitemapPath() . $sitemapBuild . '/';

        return $sitemapPath . sprintf(self::SITEMAP_FILE_NAME, $part);
    }

    /**
     * Get base sitemap path
     *
     * @return string
     */
    protected function getBaseSitemapPath()
    {
        $path = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . self::SITEMAP_DIR_NAME . '/';

        if ( !file_exists($path) )
        {
            mkdir($path, 0777);
        }

        return $path;
    }

    /**
     * Escape url
     *
     * @param string $url
     * @return string
     */
    protected function escapeSitemapUrl($url)
    {
        return htmlspecialchars($url, ENT_QUOTES | ENT_XML1);
    }

    /**
     * Build sitemap
     *
     * @return void
     */
    public function buildSitemap()
    {
        $urls = $this->sitemapDao->findUrlList(self::SITEMAP_MAX_URLS_IN_FILE);
        $sitemapBuild = (int) OW::getConfig()->getValue('base', 'seo_sitemap_last_build') + 1;
        $entities = $this->getSitemapEntities();
        $sitemapIndex = (int) OW::getConfig()->getValue('base', 'seo_sitemap_index');
        $sitemapPath = $this->getBaseSitemapPath() . $sitemapBuild . '/';

        if ( !file_exists($sitemapPath) )
        {
            mkdir($sitemapPath, 0777);
        }

        if ( $urls )
        {
            // generate parts of sitemap
            $processedUrls   = [];
            $defaultLanguage = BOL_LanguageService::getInstance()->findDefaultLanguage();
            $activeLanguages = BOL_LanguageService::getInstance()->findActiveList();
            $activeLanguagesCount = count($activeLanguages);

            // process urls
            foreach( $urls as $urlDto )
            {
                if ( !isset($entities[$urlDto->entityType]) )
                {
                    continue;
                }

                $languageList = array();

                if ( $activeLanguagesCount > 1 )
                {
                    foreach( $activeLanguages as $language )
                    {
                        if ( $language->id !== $defaultLanguage->id )
                        {
                            $languageList[] = array(
                                'url' => strstr($urlDto->url, '?')
                                    ? $this->escapeSitemapUrl($urlDto->url . '&language_id=' . $language->id)
                                    : $this->escapeSitemapUrl($urlDto->url . '?language_id=' . $language->id),
                                'code' => $language->tag
                            );
                        }
                        else
                        {
                            $languageList[] = array(
                                'url' => $this->escapeSitemapUrl($urlDto->url),
                                'code' => $language->tag
                            );
                        }
                    }
                }

                $processedUrls[] = array(
                    'url' => $this->escapeSitemapUrl($urlDto->url),
                    'changefreq' => $entities[$urlDto->entityType]['changefreq'],
                    'priority' => $entities[$urlDto->entityType]['priority'],
                    'languageList' => $languageList
                );

                $this->sitemapDao->deleteById($urlDto->id);
            }

            if ( $processedUrls )
            {
                $view = new OW_View();
                $view->setTemplate(OW::getPluginManager()->getPlugin('base')->getViewDir() . 'sitemap_part.xml');
                $view->assign('urls', $processedUrls);

                // save data in a file
                file_put_contents($sitemapPath .
                        sprintf(self::SITEMAP_FILE_NAME, $sitemapIndex + 1), gzencode($view->render(), 6));

                OW::getConfig()->saveConfig('base', 'seo_sitemap_index', $sitemapIndex + 1);
            }

            return;
        }

        // generate a main sitemap file
        $sitemapParts = array();

        if ( $sitemapIndex )
        {
            $lastModDate = date('c', time());

            for ($i = 1; $i <= $sitemapIndex; $i++) {
                $sitemapParts[] = array(
                    'url' => $this->escapeSitemapUrl($this->getSitemapUrl($i)),
                    'lastmod' => $lastModDate
                );
            }
        }

        $view = new OW_View();
        $view->setTemplate(OW::getPluginManager()->getPlugin('base')->getViewDir() . 'sitemap.xml');
        $view->assign('urls', $sitemapParts);

        // save data in a file
        file_put_contents($sitemapPath .
                sprintf(self::SITEMAP_FILE_NAME, ''), gzencode($view->render(), 6));

        // update configs
        OW::getConfig()->saveConfig('base', 'seo_sitemap_index', 0);
        OW::getConfig()->saveConfig('base', 'seo_sitemap_last_start', time());
        OW::getConfig()->saveConfig('base', 'seo_sitemap_last_build', $sitemapBuild);

        // remove a previous build
        $previousBuldPath = $this->getBaseSitemapPath() . ($sitemapBuild - 1) . '/';
        if ( file_exists($previousBuldPath) )
        {
            UTIL_File::removeDir($previousBuldPath);
        }

        // clear entities
        foreach ($entities as $entityType => $entityData)
        {
            foreach ($entityData['items'] as $item)
            {
                $this->setSitemapEntityDataFetched($entityType, $item['name'], false);
            }
        }
    }

    /**
     * Is sitemap ready for the next build
     *
     * @return boolean
     */
    public function isSitemapReadyForNextBuild()
    {
        $lastStart  = (int) OW::getConfig()->getValue('base', 'seo_sitemap_last_start');
        $scheduleUpdate = OW::getConfig()->getValue('base', 'seo_sitemap_schedule_update');

        if ( !$lastStart )
        {
            return true;
        }

        $secondsInDay = 86400;

        switch($scheduleUpdate)
        {
            case self::SITEMAP_UPDATE_MONTHLY :
                $delaySeconds = $secondsInDay * 30;
                break;

            case self::SITEMAP_UPDATE_WEEKLY :
                $delaySeconds = $secondsInDay * 6;
                break;

            case self::SITEMAP_UPDATE_DAILY:
            default:
                $delaySeconds = $secondsInDay;
        }

        return $lastStart - time() >= $delaySeconds;
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
     * @param array $items
     * @param float $priority
     * @param string $changeFreq
     * @return void
     */
    public function addSitemapEntity($langPrefix, $label, $entityType, array $items, $description = null, $priority = 0.5, $changeFreq = self::SITEMAP_ITEM_UPDATE_WEEKLY)
    {
        $entities = $this->getSitemapEntities();

        if ( !array_key_exists($entityType, $entities) )
        {
            // process items
            $processedItems = array();
            foreach ($items as $item) {
                $processedItems[] = array(
                    'name' => $item,
                    'data_fetched' => false
                );
            }

            $entities[$entityType] = array(
                'lang_prefix' => $langPrefix,
                'label' => $label,
                'description' => $description,
                'items' => $processedItems,
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

    /**
     * Add sitemap url
     *
     * @param string $url
     * @param string $entityType
     * @return void
     */
    public function addSiteMapUrl($url, $entityType)
    {
        $sitemapDto = new BOL_Sitemap();
        $sitemapDto->url = $url;
        $sitemapDto->entityType = $entityType;


        $this->sitemapDao->save($sitemapDto);
    }

    /**
     * Set sitemap entity data fetched
     *
     * @param string $entityType
     * @param string $itemName
     * @param boolean $dataFetched
     * @return void
     */
    public function setSitemapEntityDataFetched($entityType, $itemName, $dataFetched)
    {
        $entities = $this->getSitemapEntities();

        if ( array_key_exists($entityType, $entities) )
        {
            foreach( $entities[$entityType]['items'] as &$item )
            {

                if ($itemName == $item['name'])
                {
                    $item['data_fetched'] = $dataFetched;

                    break;
                }
            }

            OW::getConfig()->saveConfig('base', 'seo_sitemap_entities', json_encode($entities));
        }
    }
}
