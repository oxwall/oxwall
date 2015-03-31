<?php

/**
 * Data Transfer Object for `base_search_entity` table.
 *
 * @package ow.plugin.guestbook.bol
 * @since 1.0
 */
class BOL_SearchEntity extends OW_Entity
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
     * Entity text
     * @var string
     */
    public $entityText;

    /**
     * Entity active
     * @var integer
     */
    public $entityActive;

    /**
     * Entity created
     * @var integer
     */
    public $entityCreated;
}