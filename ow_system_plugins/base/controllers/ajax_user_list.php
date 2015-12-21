<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 12/9/15
 * Time: 5:36 PM
 */

class BASE_CTRL_AjaxUserList extends OW_ActionController
{
    public function loadList()
    {
        if ( !OW::getRequest()->isAjax() || empty($_POST['command']) )
        {
            exit(json_encode(array('result' => false)));
        }

        $command = !empty($_POST['command']) ? $_POST['command'] : null;
        $listKey = !empty($_POST['listKey']) ? (int)$_POST['listKey'] : null;
        $additionalParams = !empty($_POST['additionalParams']) ? $_POST['additionalParams'] : array();
        $excludeList = !empty($_POST['excludeList']) ? $_POST['excludeList'] : null;
        $count = !empty($_POST['count']) ? (int)$_POST['count'] : 0;
        $startFrom = !empty($_POST['startFrom']) ? (int)$_POST['startFrom'] : 1;
        $page = $startFrom;
        $enableActions = !empty($_POST['enableActions']) ? $_POST['enableActions'] : false;

        $from = ($startFrom-1) * $count;

        switch ( $command )
        {
            case 'getNext':
                break;

            case 'getPrev':
                if ( $startFrom == 0 )
                {
                    exit(json_encode(array('result' => true, 'items' => array(), 'content' => '' )));
                }
                $from = ($startFrom-1) * $count;
                break;
        }
        OW::getRouter()->urlForRoute();
        list($list, $count) = BOL_UserService::getInstance()->getDataForUsersList($listKey, $from, $count, $excludeList, $additionalParams);

        if ( empty($list) )
        {
            exit(json_encode(array('result' => true, 'items' => array(), 'content' => '' )));
        }

        $idList = array();

        foreach ( $list as $dto )
        {
            $idList[] = $dto->id;
        }

        $cmp = OW::getClassInstance('BASE_CMP_BaseUserList', $listKey, $idList, $enableActions, array_merge($additionalParams, array('page' => $page)));
        exit(json_encode(array('result' => true, 'items' => $idList, 'content' => $cmp->render())));
    }
}