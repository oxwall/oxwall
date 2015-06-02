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
class BASE_CTRL_Attachment extends OW_ActionController
{
    /**
     * @var BOL_AttachmentService
     */
    private $service;

    public function __construct()
    {
        $this->service = BOL_AttachmentService::getInstance();
    }

    public function delete( $params )
    {
        exit;
    }

    public function addLink()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $url = $_POST['url'];

        $urlInfo = parse_url($url);
        if ( empty($urlInfo['scheme']) )
        {
            $url = 'http://' . $url;
        }

        $url = str_replace("'", '%27', $url);

        $oembed = UTIL_HttpResource::getOEmbed($url);
        $oembedCmp = new BASE_CMP_AjaxOembedAttachment($oembed);

        $attacmentUniqId = $oembedCmp->initJs();

        unset($oembed['allImages']);

        $response = array(
            'content' => $this->getMarkup($oembedCmp->render()),
            'type' => 'link',
            'result' => $oembed,
            'attachment' => $attacmentUniqId
        );

        echo json_encode($response);

        exit;
    }

    private function getMarkup( $html )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $markup = array();
        $markup['html'] = $html;

        $onloadScript = $document->getOnloadScript();
        $markup['js'] = empty($onloadScript) ? null : $onloadScript;

        $styleDeclarations = $document->getStyleDeclarations();
        $markup['css'] = empty($styleDeclarations) ? null : $styleDeclarations;

        return $markup;
    }
    /* 1.6.1 divider */

    public function addPhoto( $params )
    {
        $resultArr = array('result' => false, 'message' => 'General error');
        $bundle = $_GET['flUid'];

        if ( OW::getUser()->isAuthenticated() && !empty($_POST['flUid']) && !empty($_POST['pluginKey']) && !empty($_FILES['attachment']) )
        {
            $pluginKey = $_POST['pluginKey'];
            $item = $_FILES['attachment'];

            try
            {
                $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle, array('jpg', 'jpeg', 'png', 'gif'), 2000);
                $resultArr['result'] = true;
                $resultArr['url'] = $dtoArr['url'];
            }
            catch ( Exception $e )
            {
                $resultArr['message'] = $e->getMessage();
            }
        }

        exit("<script>if(parent.window.owPhotoAttachment['" . $bundle . "']){parent.window.owPhotoAttachment['" . $bundle . "'].updateItem(" . json_encode($resultArr) . ");}</script>");
    }

    public function addFile()
    {
        $respondArr = array();
        $bundle = $_GET['flUid'];
        if ( OW::getUser()->isAuthenticated() && !empty($_POST['flData']) && !empty($_POST['pluginKey']) && !empty($_FILES['ow_file_attachment']) )
        {
            $respondArr['noData'] = false;
            $respondArr['items'] = array();
            $nameArray = json_decode(urldecode($_POST['flData']), true);
            $pluginKey = $_POST['pluginKey'];

            $finalFileArr = array();

            foreach ( $_FILES['ow_file_attachment'] as $key => $items )
            {
                foreach ( $items as $index => $item )
                {
                    if ( !isset($finalFileArr[$index]) )
                    {
                        $finalFileArr[$index] = array();
                    }

                    $finalFileArr[$index][$key] = $item;
                }
            }

            foreach ( $finalFileArr as $item )
            {
                try
                {
                    $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle);
                    $respondArr['result'] = true;
                }
                catch ( Exception $e )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => false, 'message' => $e->getMessage());
                }

                if ( !array_key_exists($nameArray[$item['name']], $respondArr['items']) )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => true, 'dbId' => $dtoArr['dto']->getId());
                }
            }

            $items = $this->service->getFilesByBundleName($pluginKey, $bundle);

            OW::getEventManager()->trigger(new OW_Event('base.attachment_uploaded', array('pluginKey' => $pluginKey, 'uid' => $bundle, 'files' => $items)));
        }
        else
        {
            $respondArr = array('result' => false, 'message' => 'General error', 'noData' => true);
        }

        exit("<script>if(parent.window.owFileAttachments['" . $bundle . "']){parent.window.owFileAttachments['" . $bundle . "'].updateItems(" . json_encode($respondArr) . ");}</script>");
    }

    public function deleteFile()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            exit;
        }

        $fileId = !empty($_POST['id']) ? (int) $_POST['id'] : -1;
        $this->service->deleteAttachment(OW::getUser()->getId(), $fileId);

        exit;
    }
}
