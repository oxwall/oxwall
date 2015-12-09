<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 12/8/15
 * Time: 3:28 PM
 */

class BASE_CMP_AjaxAdvancedUserList extends OW_Component
{
    protected $listKey;
    protected $list;
    protected $actions;
    protected $page;
    protected $params;

    public function __construct($listKey, $list, $page = 1, $actions = false, $aditionalParams = array())
    {
        parent::__construct();

        $this->listKey = $listKey;
        $this->list = $list;
        $this->page = $page;
        $this->actions = $actions;
        $this->params = $aditionalParams;
    }

    public function onBeforeRender()
    {

        parent::onBeforeRender();

        $idList = array();

        foreach ( $this->list as $id )
        {
            $idList[$id] = $id;
        }

        $jsParams = array(
            'excludeList' => $idList,
            'respunderUrl' => OW::getRouter()->urlForRoute('usearch.load_list_action'),
            'listKey' => $this->listKey,
            'page' => $this->page,
            'count' => !empty($aditionalParams['limit']) ? $aditionalParams['limit'] : 20,
            'params' => $aditionalParams

        );

        $script = ' USEARCH_ResultList.init('.  json_encode($jsParams).', $(".ow_search_results_photo_gallery_container")); ';

        OW::getDocument()->addOnloadScript($script);

        $cmp = OW::getClassInstance('BASE_CMP_AdvancedUserList', $this->listKey, $this->list, $this->actions, $this->aditionalParams);

        $this->addComponent('userList', $cmp);
        $this->assign('list', $this->list);
        $this->assign('page', $this->page);
    }
}
