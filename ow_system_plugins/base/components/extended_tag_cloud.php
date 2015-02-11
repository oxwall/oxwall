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
 * Extended tag cloud component.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ExtendedTagCloud extends OW_Component
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
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $routeName;
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
    public function __construct( $entityType, $url, $tagsCount = null )
    {
        parent::__construct();

        $this->service = BOL_TagService::getInstance();
        $this->entityType = trim($entityType);
        $this->url = trim($url);
        $this->tagsCount = $tagsCount;
    }

    /**
     * @see OW_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        // find tags to show
        if ( $this->entityId !== null )
        {
            $tags = $this->service->findEntityTagsWithPopularity($this->entityId, $this->entityType);
        }
        else
        {
            if ( $this->tagsCount === null )
            {
                $this->tagsCount = $this->service->getConfig(BOL_TagService::CONFIG_DEFAULT_EXTENDED_TAGS_COUNT);
            }

            $tags = $this->service->findMostPopularTags($this->entityType, $this->tagsCount);
        }

        // get font sizes from configs
        $minFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MIN_FONT_SIZE);
        $maxFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MAX_FONT_SIZE);

        // get min and max tag's items count
        $minCount = null;
        $maxCount = null;

        foreach ( (!empty($tags) ? $tags : array() ) as $tag )
        {
            if ( $minCount === null )
            {
                $minCount = (int) $tag['itemsCount'];
                $maxCount = (int) $tag['itemsCount'];
            }

            if ( (int) $tag['itemsCount'] < $minCount )
            {
                $minCount = (int) $tag['itemsCount'];
            }

            if ( (int) $tag['itemsCount'] > $maxCount )
            {
                $maxCount = (int) $tag['itemsCount'];
            }
        }

        // prepare array to assign
        foreach ( (!empty($tags) ? $tags : array() ) as $key => $value )
        {
            $tags[$key]['url'] = ($this->routeName === null) ? OW::getRequest()->buildUrlQueryString($this->url, array('tag' => $value['tagLabel'])) : OW::getRouter()->urlForRoute($this->routeName, array('tag' => $value['tagLabel']));

            $fontSize = ($maxCount === $minCount ? ($maxFontSize / 2) : floor(((int) $value['itemsCount'] - $minCount) / ($maxCount - $minCount) * ($maxFontSize - $minFontSize) + $minFontSize));

            $tags[$key]['size'] = $fontSize;
            $tags[$key]['lineHeight'] = $fontSize + 4;
        }

        $this->assign('tags', $tags);
    }

    /**
     * Sets route name for tag items.
     * Route should be added to router and contain var - `tag`.
     * 
     * @param string $routeName
     * @return BASE_CMP_TagCloud
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = trim($routeName);
        return $this;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}