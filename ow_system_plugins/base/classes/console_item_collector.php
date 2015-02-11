<?php

class BASE_CLASS_ConsoleItemCollector extends BASE_CLASS_EventCollector
{
   public function addItem( $item, $order = null )
    {
        $this->add(array(
            'item' => $item,
            'order' => $order
        ));
    }
}