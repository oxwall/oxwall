<?php

class UPDATE_SeoService
{
    /**
     * Sitemap item update weekly
     */
    const SITEMAP_ITEM_UPDATE_WEEKLY = BOL_SeoService::SITEMAP_ITEM_UPDATE_WEEKLY;

    /**
     * Sitemap item update daily
     */
    const SITEMAP_ITEM_UPDATE_DAILY = BOL_SeoService::SITEMAP_ITEM_UPDATE_DAILY;

    /**
     * Instance
     *
     * @var UPDATE_SeoService
     */
    private static $classInstance;

    /**
     * Service
     *
     * @var BOL_SeoService
     */
    private $service;

    private function __construct()
    {
        $this->service = BOL_SeoService::getInstance();
    }

    /**
     * Get instance
     *
     * @return UPDATE_SeoService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Add sitemap entity
     *
     * @param string $langPrefix
     * @param string $label
     * @param string $entityType
     * @param array $items
     * @param string $description
     * @param float $priority
     * @param string $changeFreq
     * @return void
     */
    public function addSitemapEntity($langPrefix, $label, $entityType, array $items, $description = null, $priority = 0.5, $changeFreq = self::SITEMAP_ITEM_UPDATE_WEEKLY)
    {
        $this->service->addSitemapEntity($langPrefix, $label, $entityType, $items, $description, $priority, $changeFreq);
    }

    /**
     * Remove entity from sitemap
     *
     * @param string $entityType
     * @return void
     */
    public function removeSitemapEntity($entityType)
    {
        $this->service->removeSitemapEntity($entityType);
    }
}
