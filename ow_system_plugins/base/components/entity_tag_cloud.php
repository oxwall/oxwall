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
 * Default tag cloud component.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_EntityTagCloud extends BASE_CMP_TagCloud
{
    /**
     * @var integer
     */
    protected $entityId;
    /**
     * @var string
     */
    protected $entityType;
    /**
     * @var integer
     */
    protected $tagsCount;
    /**
     * @var BOL_TagService
     */
    protected $service;

    /**
     * Constructor.
     *
     * @param string $entityType
     * @param string $url
     * @param integer $tagsCount
     */
    public function __construct( $entityType, $url = null, $tagsCount = null )
    {
        parent::__construct();
        $this->service = BOL_TagService::getInstance();
        $this->entityType = trim($entityType);
        $this->url = trim($url);
        $this->tagsCount = $tagsCount;

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'tag_cloud.html');
    }

    /**
     * Sets entity id for tag selection.
     * If set only entity item's tags are displayed.
     *
     * @param integer $entityId
     * @return BASE_CMP_EntityTagCloud
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return integer
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    /**
     * @param integer $tagsCount
     * @return BASE_CMP_EntityTagCloud
     */
    public function setTagsCount( $tagsCount )
    {
        $this->tagsCount = $tagsCount;
        return $this;
    }

    /**
     * @see OW_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        if ( $this->entityId !== null )
        {
            $this->tagList = $this->service->findEntityTagsWithPopularity($this->entityId, $this->entityType);
        }
        else
        {
            if ( $this->tagsCount === null )
            {
                $this->tagsCount = $this->service->getConfig(BOL_TagService::CONFIG_DEFAULT_TAGS_COUNT);
            }

            $this->tagList = $this->service->findMostPopularTags($this->entityType, $this->tagsCount);
        }

        parent::onBeforeRender();
    }
}