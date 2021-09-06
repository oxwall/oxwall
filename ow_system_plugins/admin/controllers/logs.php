<?php

class ADMIN_CTRL_Logs extends ADMIN_CTRL_Abstract
{
    const ENTRIES_PER_PAGE = 50;

    /**
     * @var BOL_LogService
     */
    protected $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = BOL_LogService::getInstance();
    }

    public function index()
    {
        $pageParam = (int) ($_GET['page'] ?? null);
        $typeParam = $_GET['type'] ?? null;
        $keyParam = $_GET['key'] ?? null;

        $page = $pageParam > 0 ? $pageParam - 1 : 0;

        $first = self::ENTRIES_PER_PAGE * $page;

        if ( !empty($typeParam) )
        {
            $entries = $this->service->findByTypePaginated($typeParam, $first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countByType($typeParam);
        }
        elseif ( !empty($keyParam) )
        {
            $entries = $this->service->findByKeyPaginated($keyParam, $first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countByKey($keyParam);
        }
        else
        {
            $entries = $this->service->findAll($first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countAll();
        }

        $totalPages = floor($totalEntries / self::ENTRIES_PER_PAGE);

        if ( empty($entries) )
        {
            $this->assign('isEmpty', true);
            return;
        }

        $processedEntries = $this->service->processEntries($entries);

        $paging = new BASE_CMP_Paging($page + 1, $totalPages, 10);
        $this->assign('paging', $paging->render());

        $this->assign('entries', $processedEntries);
    }

    public function entry( $params )
    {
        $entryId = (int) $params['id'];

        if ( $entryId <= 0 )
        {
            $this->redirect(OW::getRouter()->urlForRoute('admin_developer_tools_logs'));
            return;
        }

        $entry = $this->service->findById($entryId);

        if ( !$entry )
        {
            $this->redirect(OW::getRouter()->urlForRoute('admin_developer_tools_logs'));
            return;
        }

        $logEntryCustomViewEvent = new OW_Event(OW_EventManager::ON_LOG_ENTRY_CUSTOM_VIEW, array(
            'entry' => $entry
        ));

        OW::getEventManager()->trigger($logEntryCustomViewEvent);

        $customView = $logEntryCustomViewEvent->getData();

        if ( !empty($customView) && is_string($customView) )
        {
            $this->assign('customView', $customView);
        }
        else
        {
            list($entryProcessed) = $this->service->processEntries([ $entry ]);
            $this->assign('entry', $entryProcessed);
        }
    }
}
