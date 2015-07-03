<?php

/**
 * Data Transfer Object for `base_site_statistic` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SiteStatistic extends OW_Entity
{
    /**
     * Entity type
     * @var string
     */
    public $entityType;

    /**
     * Entity id
     * @var string
     */
    public $entityId;

    /**
     * Entity count
     * @var integer
     */
    public $entityCount;

    /**
     * TimeStamp
     * @var integer
     */
    public $timeStamp;
}