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
 * Data Transfer Object for `base_question` table.  
 * 
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Question extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $sectionName;
    /**
     * @var string
     */
    public $accountTypeName;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $presentation;
    /**
     * @var integer
     */
    public $required = 0;
    /**
     * @var integer
     */
    public $onJoin = 0;
    /**
     * @var integer
     */
    public $onEdit = 0;
    /**
     * @var integer
     */
    public $onSearch = 0;
    /**
     * @var integer
     */
    public $onView = 0;
    /**
     * @var integer
     */
    public $base = 0;
    /**
     * @var integer
     */
    public $removable = 1;
    /**
     * @var integer
     */
    public $sortOrder;
    /**
     * @var integer
     */
    public $columnCount = 1;
    /**
     * @var string
     */
    public $parent;
    /**
     * @var string
     */
    public $custom;
}

