<?php

class BASE_CLASS_ConsoleListEvent extends OW_Event
{
    private $itemsList = array();

    public function __construct( $name, $params, $data)
    {
        parent::__construct($name, $params, $data);

        $this->itemsList = array();
    }

    public function addItem( $item, $id = null )
    {
        $this->itemsList[] = array(
            'html' => $item,
            'id' => $id
        );
    }

    public function getList()
    {
        return $this->itemsList;
    }
}
