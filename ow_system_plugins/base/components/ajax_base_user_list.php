<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 12/8/15
 * Time: 3:28 PM
 */

class BASE_CMP_AjaxBaseUserList extends OW_Component
{
    protected $listKey;
    protected $list;
    protected $actions;
    protected $page;
    protected $usersPrePage;
    protected $params;

    public function __construct($listKey, $list, $page = 1, $usersPrePage = 20, $actions = false, $additionalParams = array())
    {
        parent::__construct();

        $this->listKey = $listKey;
        $this->list = $list;
        $this->page = $page;
        $this->actions = $actions;
        $this->usersPrePage = usersPrePage;
        $this->params = $additionalParams;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $idList = array();

        foreach ( $this->list as $id )
        {
            $idList[] = $id;
        }

        $jsParams = array(
            'excludeList' => $idList,
            'respunderUrl' => OW::getRouter()->urlForRoute('base.ajax_user_list'),
            'listKey' => $this->listKey,
            'page' => $this->page,
            'count' => !empty($this->usersPrePage) ? $this->usersPrePage : 20,
            'params' => $this->params
        );

        $script = ' AjaxUserList.init('.  json_encode($jsParams).', $(".ow_user_list_photo_gallery_container")); ';
        OW::getDocument()->addOnloadScript($script);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'ajax_user_list.js');

        $cmp = OW::getClassInstance('BASE_CMP_BaseUserList', $this->listKey, $this->list, $this->actions, array_merge( $this->params, array( 'page' => $this->page ) ));

        $this->addComponent('userList', $cmp);
        $this->assign('list', $this->list);
        $this->assign('page', $this->page);
    }
}
