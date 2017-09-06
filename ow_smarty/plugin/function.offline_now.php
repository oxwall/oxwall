<?php

function smarty_function_offline_now( $params, $smarty )
{
    $chatNowMarkup = '';
    if ( OW::getUser()->isAuthenticated() && isset($params['userId']) && OW::getUser()->getId() != $params['userId'])
    {
        $allowChat = OW::getEventManager()->call('base.online_now_click', array('userId'=>OW::getUser()->getId(), 'onlineUserId'=>$params['userId']));

        if ($allowChat)
        {
            $chatNowMarkup = '<span id="ow_chat_now_'.$params['userId'].'" class="ow_lbutton ow_green" onclick="OW.trigger(\'base.online_now_click\', [ \'' . $params['userId'] . '\' ] );" >' . OW::getLanguage()->text('mailbox', 'user_list_chat_offline') . '</span><span id="ow_preloader_content_'.$params['userId'].'" class="ow_preloader_content ow_hidden"></span>';
        }
    }

    $buttonMarkup = '<div class="ow_miniic_live">'.$chatNowMarkup.'</div>';

    return $buttonMarkup;
}
?>
