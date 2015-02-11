<?php

class BASE_CLASS_ConsoleDataEvent extends OW_Event
{
    public function __construct( $name, $params, $data)
    {
        parent::__construct($name, $params);

        $this->data = $data;
    }

    public function getItemData( $key = null )
    {
        if ( $key === null )
        {
            return $this->data;
        }

        return empty($this->data[$key]) ? array() : $this->data[$key];
    }

    public function setItemData( $key, $data )
    {
        $this->data[$key] = $data;
    }


}
