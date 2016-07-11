<?php

class UPDATE_AuthorizationService
{
    /**
     * Singleton instance.
     *
     * @var BOL_AuthorizationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->authorizationService = BOL_AuthorizationService::getInstance();
    }
    private $authorizationService;


    public function findGroupIdByName( $name )
    {
        return $this->authorizationService->findGroupIdByName($name);
    }
    
    public function findAction( $groupName, $actionName )
    {
        return $this->authorizationService->findAction($groupName, $actionName);
    }

    public function deleteGroup( $groupName )
    {
        $this->authorizationService->deleteGroup($groupName);
    }

    public function addGroup( $name, $moderated = true )
    {
        if ( $this->authorizationService->findGroupByName($name) !== null )
        {
            trigger_error('Cant add group `' . $name . '`! Duplicate entry!', E_NOTICE);
            return;
        }

        $group = new BOL_AuthorizationGroup();
        $group->name = $name;
        $group->moderated = $moderated;

        $this->authorizationService->saveGroup($group);
    }

    /**
     *
     * @param BOL_AuthorizationAction $action
     * @param array $labels ex.: array('en' => 'Colour', 'en-US' => 'Color')
     */
    public function addAction( BOL_AuthorizationAction $action, array $labels )
    {
        $this->authorizationService->addAction($action, $labels);
    }

    public function deleteAction( $actionId )
    {
        $this->authorizationService->deleteAction($actionId);
    }
}