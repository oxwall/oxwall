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
 * Master page is a common markup "border" for controller's output.
 * It includes menus, sidebar, header, etc.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_MobileMasterPage extends OW_MasterPage
{
    /*
     * List of default master page templates.
     */
    const TEMPLATE_GENERAL = "mobile_general";
    const TEMPLATE_BLANK = "mobile_blank";

    /**
     * List of button params
     */
    const BTN_DATA_ID = "id";
    const BTN_DATA_CLASS = "class";
    const BTN_DATA_HREF = "href";
    const BTN_DATA_EXTRA = "extraString";

    private $buttonData;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->buttonData = array(
            "left" => array(self::BTN_DATA_ID => "owm_header_left_btn", self::BTN_DATA_CLASS => null, self::BTN_DATA_HREF => "javascript://",
                self::BTN_DATA_EXTRA => ""),
            "right" => array(self::BTN_DATA_ID => "owm_header_right_btn", self::BTN_DATA_CLASS => null, self::BTN_DATA_HREF => "javascript://",
                self::BTN_DATA_EXTRA => "")
        );
    }

    /**
     * Master page init actions. Template assigning, registering standard cmps, etc.
     * Default version works for `general` master page. 
     */
    protected function init()
    {
        
    }

    /**
     * @param array $data
     */
    public function setLButtonData( array $data )
    {
        $this->buttonData["left"] = array_merge($this->buttonData["left"], $data);
    }

    /**
     * @param array $data
     */
    public function setRButtonData( array $data )
    {
        $this->buttonData["right"] = array_merge($this->buttonData["right"], $data);
    }

    public function onBeforeRender()
    {
        if ( $this->getTemplate() === null )
        {
            $this->setTemplate(OW::getThemeManager()->getMasterPageTemplate(self::TEMPLATE_GENERAL));
        }

        $this->addComponent("signIn", new BASE_MCMP_SignIn());
        $this->addComponent("topMenu", new BASE_MCMP_TopMenu());
        $this->addComponent("bottomMenu", new BASE_MCMP_BottomMenu());
        $this->assign("buttonData", $this->buttonData);

        parent::onBeforeRender();
    }

    public function setTemplate( $template )
    {
        //TODO remove dirty hack for backcompat
        if ( substr(basename($template), 0, strlen(self::TEMPLATE_BLANK)) == self::TEMPLATE_BLANK )
        {
            $this->buttonData = array("left" => array(), "right" => array());
        }

        parent::setTemplate($template);
    }
}
