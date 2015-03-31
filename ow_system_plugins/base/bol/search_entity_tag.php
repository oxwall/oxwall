<?php

/**
 * Data Transfer Object for `base_search_entity_tag` table.
 *
 * @package ow.plugin.guestbook.bol
 * @since 1.0
 */
class BOL_SearchEntityTag extends OW_Entity
{
    /**
     * Entity tag
     * @var string
     */
    public $entityTag;

    /**
     * Entity id
     * @var string
     */
    public $entityId;
}