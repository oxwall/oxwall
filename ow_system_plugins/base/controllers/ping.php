<?php

class BASE_CTRL_Ping extends OW_ActionController
{
    const PING_EVENT = 'base.ping';

    public function index()
    {
        $request = json_decode($_POST['request'], true);
        $stack = $request['stack'];

        $responseStack = array();

        foreach ( $stack as $c )
        {
            $command = strip_tags(trim($c['command']));
            $params  = $c['params'];

            $event = new OW_Event(self::PING_EVENT . '.' . $command, $params);
            OW::getEventManager()->trigger($event);

            $event = new OW_Event(self::PING_EVENT, $c, $event->getData());
            OW::getEventManager()->trigger($event);

            $responseStack[] = array(
                'command' => $command,
                'result' => $event->getData()
            );
        }

        echo json_encode(array(
            'stack' => $responseStack
        ));

        exit;
    }
}