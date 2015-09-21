<?php

class BASE_MCMP_BaseUserList extends BASE_MCMP_UserList
{    
    const EVENT_GET_FIELDS = "base.user_list.get_fields";
    
    protected $listKey;

    public function __construct($listKey, $list, $showOnline) //__construct( $listKey, $showOnline = false, $excludeList = array(), $count = 10 )
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $showOnline);
    }
    
    public function getFields( $userIdList )
    {        
        $fields = array();        
        
        foreach ( $userIdList as $id )
        {
            $fields[$id] = array();
        }
        
        $params = array(
            'list' => $this->listKey,
            'userIdList' => $userIdList  );

        $event = new OW_Event( self::EVENT_GET_FIELDS, $params, $fields);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        
        return $data;
    }
}