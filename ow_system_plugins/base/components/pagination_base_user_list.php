<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 12/8/15
 * Time: 3:28 PM
 */

class BASE_CMP_PaginationBaseUserList extends OW_Component
{
    protected $listKey;
    protected $list;
    protected $usersPerPage;
    protected $actions;
    protected $page;
    protected $itemsCount;
    protected $params;

    public function __construct($listKey, $list, $page = 1, $itemsCount = 0, $usersPrePage= 20, $actions = false, $additionalParams = array())
    {
        parent::__construct();

        $this->listKey = $listKey;
        $this->list = $list;
        $this->page = $page;
        $this->usersPerPage = $usersPrePage;
        $this->itemsCount = $itemsCount;
        $this->actions = $actions;
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

        $cmp = OW::getClassInstance( 'BASE_CMP_BaseUserList', $this->listKey, $this->list, $this->actions, array_merge( $this->params, array( 'page' => $this->page ) ) );

        $pagination = OW::getClassInstance( 'BASE_CMP_Paging',  $this->page, ceil($this->itemsCount / $this->usersPerPage), 5);

        $this->addComponent('userList', $cmp);
        $this->addComponent("pagination", $pagination );
        $this->assign('list', $this->list);
        $this->assign('page', $this->page);

    }
}
