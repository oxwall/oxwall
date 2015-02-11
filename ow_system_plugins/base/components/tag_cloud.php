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
 * Base tag cloud component.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_TagCloud extends OW_Component
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $routeName;
    /**
     * @var array
     */
    protected $tagList;
    /**
     * @var BOL_TagService
     */
    protected $service;

    /**
     * Constructor.
     *
     * @param array<count,label> $tagList
     *
     */
    public function __construct( array $tagList = null, $url = null )
    {
        parent::__construct();

        $this->tagList = $tagList;
        $this->url = $url;
        $this->service = BOL_TagService::getInstance();
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

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl( $url )
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getTagList()
    {
        return $this->tagList;
    }

    /**
     * @param array $tagList
     */
    public function setTagList( $tagList )
    {
        $this->tagList = $tagList;
    }

    /**
     * @see OW_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        if ( $this->url === null && $this->routeName === null )
        {
            throw new LogicException();
        }

        // get font sizes from configs
        $minFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MIN_FONT_SIZE);
        $maxFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MAX_FONT_SIZE);

        // get min and max tag's items count
        $minCount = null;
        $maxCount = null;

        if ( !$this->tagList )
        {
            $this->setVisible(false);
            return;
        }

        foreach ( $this->tagList as $tag )
        {
            if ( $minCount === null )
            {
                $minCount = (int) $tag['count'];
                $maxCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] < $minCount )
            {
                $minCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] > $maxCount )
            {
                $maxCount = (int) $tag['count'];
            }
        }

        $tags = array();

        // prepare array to assign
        $list = empty($this->tagList) ? array() : $this->tagList;

        foreach ( $list as $key => $value )
        {
            if ( $value['label'] === null )
            {
                continue;
            }

            $tags[$key]['url'] = ($this->routeName === null) ? OW::getRequest()->buildUrlQueryString($this->url, array('tag' => $value['label'])) : OW::getRouter()->urlForRoute($this->routeName, array('tag' => $value['label']));

            $fontSize = ($maxCount === $minCount ? ($maxFontSize / 2) : floor(((int) $value['count'] - $minCount) / ($maxCount - $minCount) * ($maxFontSize - $minFontSize) + $minFontSize));

            $tags[$key]['size'] = $fontSize;
            $tags[$key]['lineHeight'] = $fontSize + 4;
            $tags[$key]['label'] = $value['label'];
        }

        $this->assign('tags', $tags);
    }
}
