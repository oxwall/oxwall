<?php

class BASE_CLASS_InvitationEventHandler
{
    /**
     * Class instance
     *
     * @var BASE_CLASS_InvitationEventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BASE_CLASS_InvitationEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const CONSOLE_ITEM_KEY = 'invitation';

    /**
     *
     * @var BOL_InvitationService
     */
    private $service;

    private function __construct()
    {
        $this->service = BOL_InvitationService::getInstance();
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $item = new BASE_CMP_ConsoleInvitations();

        $allInvitationCount = $this->service->findInvitationCount(OW::getUser()->getId());
        $item->setIsHidden( empty($allInvitationCount) );

        $event->addItem($item, 6);
    }

    public function addInvitation( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['entityType']) || empty($params['entityId']) || empty($params['userId']) || empty($params['pluginKey']) )
        {
            throw new InvalidArgumentException('`entityType`, `entityId`, `userId`, `pluginKey` are required');
        }

        $invitation = $this->service->findInvitation($params['entityType'], $params['entityId'], $params['userId']);

        if ( $invitation === null )
        {
            $invitation = new BOL_Invitation();
            $invitation->entityType = $params['entityType'];
            $invitation->entityId = $params['entityId'];
            $invitation->userId = $params['userId'];
            $invitation->pluginKey = $params['pluginKey'];
        }
        else
        {
            $invitation->viewed = 0;

            $dublicateParams = array(
                'originalEvent' => $event,
                'invitationDto' => $invitation,
                'oldData' => $invitation->getData()
            );

            $dublicateParams = array_merge($params, $dublicateParams);

            $dublicateEvent = new OW_Event('invitations.on_dublicate', $dublicateParams, $data);
            OW::getEventManager()->trigger($dublicateEvent);

            $data = $dublicateEvent->getData();
        }

        $invitation->timeStamp = empty($params['time']) ? time() : $params['time'];
        $invitation->active = isset($params['active']) ? (bool)$params['active'] : true;
        $invitation->action = isset($params['action']) ? $params['action'] : null;
        $invitation->setData($data);

        $this->service->saveInvitation($invitation);
    }

    public function removeInvitation( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            throw new InvalidArgumentException('`entityType` and `entityId` params are required');
        }

        $userId = empty($params['userId']) ? null : $params['userId'];
        $entityType = $params['entityType'];
        $entityId = $params['entityId'];

        if ( $userId !== null )
        {
            $this->service->deleteInvitation($entityType, $entityId, $userId);
        }
        else
        {
            $this->service->deleteInvitationByEntity($entityType, $entityId);
        }
    }

    /* Console list */

    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        $userId = OW::getUser()->getId();
        $data = $event->getItemData(self::CONSOLE_ITEM_KEY);

        $allInvitationCount = $this->service->findInvitationCount($userId);
        $newInvitationCount = $this->service->findInvitationCount($userId, false);

        $data['counter'] = array(
            'all' => $allInvitationCount,
            'new' => $newInvitationCount
        );

        $event->setItemData('invitation', $data);
    }

    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $userId = OW::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $loadItemsCount = 10;
        $invitations = $this->service->findInvitationList($userId, $params['console']['time'], $params['ids'], $loadItemsCount);
        $data['listFull'] = count($invitations) < $loadItemsCount;

        $invitationIds = array();
        foreach ( $invitations as $invitation )
        {
            $itemEvent = new OW_Event('invitations.on_item_render', array(
                'key' => 'invitation_' . $invitation->id,
                'entityType' => $invitation->entityType,
                'entityId' => $invitation->entityId,
                'pluginKey' => $invitation->pluginKey,
                'userId' => $invitation->userId,
                'viewed' => (bool) $invitation->viewed,
                'data' => $invitation->getData()
            ), $invitation->getData());

            OW::getEventManager()->trigger($itemEvent);

            $item = $itemEvent->getData();

            if ( empty($item) )
            {
                continue;
            }

            $invitationIds[] = $invitation->id;

            $event->addItem($item, $invitation->id);
        }

        $event->setData($data);

        $this->service->markViewedByIds($invitationIds);
    }

    private function processDataInterface( $params, $data )
    {
        if ( empty($data['avatar']) )
        {
            return array();
        }

        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                if ( count($key) < 2 )
                {
                    $data[$langProperty] = '-';
                }
                else
                {
                    $data[$langProperty] = OW::getLanguage()->text($key[0], $key[1], $vars);
                }
            }
        }

        if ( empty($data['string']) )
        {
            return array();
        }

        if ( !empty($data['contentImage']) )
        {
            $data['contentImage'] = is_string($data['contentImage'])
                ? array( 'src' => $data['contentImage'] )
                : $data['contentImage'];
        }
        else
        {
            $data['contentImage'] = null;
        }
        
        if ( !empty($data["avatar"]["userId"]) )
        {
            $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($data["avatar"]["userId"]));
            $data["avatar"] = $avatarData[$data["avatar"]["userId"]];
        }

        $data['contentImage'] = empty($data['contentImage']) ? array() : $data['contentImage'];
        $data['toolbar'] = empty($data['toolbar']) ? array() : $data['toolbar'];
        $data['key'] = isset($data['key']) ? $data['key'] : $params['key'];
        $data['viewed'] = isset($params['viewed']) && !$params['viewed'];

        return $data;
    }

    public function renderItem( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if (is_string($data) )
        {
            return;
        }

        $interface = $this->processDataInterface($params, $data);

        if ( empty($interface) )
        {
            $event->setData(null);
            return;
        }

        $item = new BASE_CMP_InvitationItem();
        $item->setAvatar($interface['avatar']);
        $item->setContent($interface['string']);
        $item->setKey($interface['key']);
        $item->setToolbar($interface['toolbar']);
        $item->setContentImage($interface['contentImage']);

        if ( $interface['viewed'] )
        {
            $item->addClass('ow_console_new_message');
        }

        $event->setData($item->render());
    }


    public function sendList( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];

        $invitations = $this->service->findInvitationListForSend($userIdList);
        $invitationIds = array();

        foreach ( $invitations as $invitation )
        {
            $event->add(array(
                'pluginKey' => $invitation->pluginKey,
                'entityType' => $invitation->entityType,
                'entityId' => $invitation->entityId,
                'userId' => $invitation->userId,
                'action' => $invitation->action,
                'time' => $invitation->timeStamp,

                'data' => $invitation->getData()
            ));

            $invitationIds[] = $invitation->id;
        }

        $this->service->markSentByIds($invitationIds);
    }


    public function pluginActivate( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setInvitationStatusByPluginKey($pluginKey, true);
    }

    public function pluginDeactivate( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setInvitationStatusByPluginKey($pluginKey, false);
    }

    public function pluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->deleteInvitationByPluginKey($pluginKey);
    }

    public function afterInits()
    {
        OW::getEventManager()->bind('invitations.on_item_render', array($this, 'renderItem'));
        OW::getEventManager()->bind('invitations.add', array($this, 'addInvitation'));

        OW::getEventManager()->bind('invitations.remove', array($this, 'removeInvitation'));
    }

    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'afterInits'));

        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'pluginActivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'pluginDeactivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'pluginUninstall'));

        OW::getEventManager()->bind('console.load_list', array($this, 'loadList'));
        OW::getEventManager()->bind('console.ping', array($this, 'ping'));
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));

        OW::getEventManager()->bind('notifications.send_list', array($this, 'sendList'));
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('invitations.add', array($this, 'addInvitation'));
        OW::getEventManager()->bind('invitations.remove', array($this, 'removeInvitation'));
    }
}