<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Admin content statistics component
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_ContentStatistic extends OW_Component
{
    /**
     * Default content group
     * @var string
     */
    protected $defaultContentGroup;

    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( $params )
    {
        parent::__construct();

        $this->defaultContentGroup = !empty($params['defaultContentGroup'])
            ? $params['defaultContentGroup']
            : null;

        $this->defaultPeriod = !empty($params['defaultPeriod'])
            ? $params['defaultPeriod']
            : BOL_SiteStatisticService::PERIOD_TYPE_TODAY;

    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // get all registered content groups
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups();

        // check the received group
        if (!array_key_exists($this->defaultContentGroup, $contentGroups) )
        {
            $this->setVisible(false);
            return false;
        }

        // get all group's entity types
        // e.g. forum-topic, forum-post, etc
        $entityTypes = $contentGroups[$this->defaultContentGroup]['entityTypes'];

        // TODO: Delete me or fix in a next version!!!
        if ( in_array('forum-post', $entityTypes) )
        {
            $key = array_search('forum-post', $entityTypes);
            unset($entityTypes[$key]);
        }

        // get detailed content types info
        $contentTypes = BOL_ContentService::getInstance()->getContentTypes();

        // get entity labels
        $entityLabels = array();
        foreach ($entityTypes as $entityType)
        {
            $entityLabels[$entityType] = $contentTypes[$entityType]['entityLabel'];
        }

        // register components
        $this->addComponent('statistics',
                new BASE_CMP_SiteStatistic('content-statistics-chart', $entityTypes, $entityLabels, $this->defaultPeriod));
    }
}

