<?php

class BASE_CMP_UserViewSection extends OW_Component
{
    public function __construct( $section, $sectionQuestions, $data, $labels, $template = 'table', $hideSection = false, $additionalParams = array() )
    {
        parent::__construct();

        $this->assign('sectionName', $section);
        $this->assign('questions', $sectionQuestions);
        $this->assign('questionsData', $data);
        $this->assign('labels', $labels);
        $this->assign('display', !$hideSection);

        switch ( $template )
        {
            case 'tabs':
                    $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'user_view_section_tabs.html' );
                break;

            default :
                    $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'user_view_section_table.html' );
        }
    }
}