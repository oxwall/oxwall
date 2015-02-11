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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class BASE_CLASS_FileAttachment extends OW_Component
{
    private $uid;
    private $inputSelector;
    private $showPreview;
    private $pluginKey;
    private $multiple;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $pluginKey, $uid )
    {
        parent::__construct();
        $this->uid = $uid;
        $this->showPreview = true;
        $this->pluginKey = $pluginKey;
        $this->multiple = true;
    }

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple( $multiple )
    {
        $this->multiple = (bool) $multiple;
    }

    public function getInputSelector()
    {
        return $this->inputSelector;
    }

    public function setInputSelector( $inputSelector )
    {
        $this->inputSelector = trim($inputSelector);
    }

    public function getShowPreview()
    {
        return $this->showPreview;
    }

    public function setShowPreview( $showPreview )
    {
        $this->showPreview = (bool) $showPreview;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = BOL_AttachmentService::getInstance()->getFilesByBundleName($this->pluginKey, $this->uid);
        $itemsArr = array();

        foreach ( $items as $item )
        {
            $itemsArr[] = array('name' => $item['dto']->getOrigFileName(), 'size' => $item['dto']->getSize(), 'dbId' => $item['dto']->getId());
        }

        $params = array(
            'uid' => $this->uid,
            'submitUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addFile'),
            'deleteUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'deleteFile'),
            'showPreview' => $this->showPreview,
            'selector' => $this->inputSelector,
            'pluginKey' => $this->pluginKey,
            'multiple' => $this->multiple,
            'lItems' => $itemsArr
        );

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
        OW::getDocument()->addOnloadScript("owFileAttachments['" . $this->uid . "'] = new OWFileAttachment(" . json_encode($params) . ");");



        $this->assign('data', array('uid' => $this->uid, 'showPreview' => $this->showPreview, 'selector' => $this->inputSelector));
    }
}
