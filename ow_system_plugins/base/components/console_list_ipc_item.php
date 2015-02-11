<?php

class BASE_CMP_ConsoleListIpcItem extends OW_Component
{
    /**
     *
     * @var BASE_CMP_ConsoleListItem
     */
    protected $consoleItem;

    protected $avatar = array(), $toolbar = array(),
            $content = '', $contentImage = array(), $url;


    public function __construct()
    {
        parent::__construct();

        $this->consoleItem = new BASE_CMP_ConsoleListItem();
    }

    public function setKey( $key )
    {
        $this->consoleItem->setKey($key);
    }

    public function getKey()
    {
        return $this->consoleItem->getKey();
    }

    public function setIsHidden( $hidden = true )
    {
        $this->consoleItem->setIsHidden($hidden);
    }

    public function getIsHidden()
    {
        return $this->consoleItem->getIsHidden();
    }

    public function addClass( $class )
    {
        $this->consoleItem->addClass($class);
    }

    public function setAvatar( $avatar )
    {
        $this->avatar = $avatar;
    }

    public function setContent( $content )
    {
        $this->content = $content;
    }

    public function setContentImage( $contentImage )
    {
        $this->contentImage = $contentImage;
    }

    public function setToolbar( $toolbar )
    {
        $this->toolbar = $toolbar;
    }

    public function setUrl( $url )
    {
        $this->url = $url;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('avatar', $this->avatar);
        $this->assign('content', $this->content);
        $this->assign('toolbar', $this->toolbar);
        $this->assign('contentImage', $this->contentImage);
        $this->assign('url', $this->url);
    }

    public function render()
    {
        $this->consoleItem->setContent(parent::render());

        return $this->consoleItem->render();
    }
}