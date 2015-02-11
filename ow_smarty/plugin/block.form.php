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
 * Smarty form block function.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_block_form( $params, $content )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty form name!');
    }

    $vr = OW_ViewRenderer::getInstance();
    
    $assignedForms = $vr->getAssignedVar('_owForms_');
    
    if ( !isset($assignedForms[$params['name']]) )
    {
        throw new InvalidArgumentException('There is no form with name `' . $params['name'] . '` !');
    }

    // mark active form
    if ( $content === null )
    {
        $vr->assignVar('_owActiveForm_', $assignedForms[$params['name']]);
        return;
    }

    /* @var $form OW_Form */
    $form = $vr->getAssignedVar('_owActiveForm_');

    if ( isset($params['decorator']) )
    {
        $viewRenderer = OW_ViewRenderer::getInstance();
        $viewRenderer->assignVar('formInfo', $form->getElementsInfo());
        $content = $viewRenderer->renderTemplate(OW::getThemeManager()->getDecorator($params['decorator']));
    }

    unset($params['decorator']);
    unset($params['name']);
    return $form->render($content, $params);
}