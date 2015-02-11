<?php

class BASE_MCMP_ProfileAbout extends OW_MobileComponent
{
    /**
     *
     * @var BOL_User
     */
    protected $user;
    protected $length = null;

    public function __construct( BOL_User $user, $length = null )
    {
        parent::__construct();
        
        $this->user = $user;
        $this->length = $length;
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
           
        $content = $this->getAboutMeContent();
        
        if ( $content === null )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $this->assign('aboutMe', $content);
        $this->assign('aboutUrl', OW::getRouter()->urlForRoute('base_about_profile', array('username' => $this->user->username)));
        
        $this->assign("previewMode", !empty($this->length));
    }
    
    protected function getAboutMeContent()
    {
        $settings = BOL_ComponentEntityService::getInstance()->findSettingList(
            'profile-BASE_CMP_AboutMeWidget', $this->user->id, array('content')
        );
        
        if ( empty($settings['content']) )
        {
            return null;
        }
        
        return $this->length === null
            ? $settings['content'] 
            : UTIL_String::truncate($settings['content'], $this->length, "...");
    }
}