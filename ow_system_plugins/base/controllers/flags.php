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
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Flags extends OW_ActionController
{

    public function index( $params )
    {
        $s = BOL_FlagService::getInstance();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = 20;

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $itemCount = $s->count('blog-post');

        $pageCount = 0;

        $type = (!empty($params['type'])) ? $params['type'] : '';

        $this->assign('type', $type);

        $this->assign('langKey', BOL_FlagService::getInstance()->findLangKey($type));

        $list = BOL_FlagService::getInstance()->findList($first, $count, $type);
        $itemCount = BOL_FlagService::getInstance()->countFlaggedItems($type);

        if ( empty($list) )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
        }

        foreach ( $list as $key => $f )
        {
            $list[$key]['spamUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'spam');
            $list[$key]['offenceUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'offence');
            $list[$key]['illegalUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'illegal');

            $uil = array_merge($list[$key]['spamUsers'], $list[$key]['offenceUsers'], $list[$key]['illegalUsers']);

            $this->assign('dl', BOL_UserService::getInstance()->getDisplayNamesForList($uil));
            $this->assign('ul', BOL_UserService::getInstance()->getUserNamesForList($uil));
        }

        $this->assign('list', $list);

        $this->addComponent('menu', $this->getMenu($type));

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $rpp), 5));
    }

    private function getMenu( $active )
    {
        $language = OW::getLanguage();

        $list = BOL_FlagService::getInstance()->findTypeList();

        $mil = array();
        $i = 0;
        foreach ( $list as $type )
        {
            $mi = new BASE_MenuItem();

            $c = BOL_FlagService::getInstance()->countFlaggedItems($type['type']);

            $a = explode('+', $type['langKey']);

            $mi->setLabel($language->text($a[0], $a[1]) . ($c > 0 ? " ($c)" : ''))
                ->setKey($type['type'])
                ->setOrder($i++)
                ->setUrl(OW::getRouter()->urlFor('BASE_CTRL_Flags', 'index', array('type' => $type['type'])));

            if ( $active == $type )
            {
                $mi->isActive(true);
            }

            $mil[] = $mi;
        }

        return new BASE_CMP_ContentMenu($mil);
    }
}