<?php
/**
 * Smarty function
 *
 * @param mixed var
 * @package OW_Smarty $smarty
 */

function smarty_function_user_link( $params, $smarty )
{
    $userService = BOL_UserService::getInstance();
    
    // default values for deleted / not found user
    $userUrl = $userService->getUserUrlForUsername('deleted-user');
    $displayName = OW::getLanguage()->text('base', 'deleted_user');
    
    if ( isset($params['id']) )
    {
        $user = $userService->findUserById($params['id']);
        
        if ( $user )
        {
            $userUrl = $userService->getUserUrlForUsername($user->getUsername());
            $displayName = $userService->getDisplayName($user->getId());
        }
    }
    else 
    {
        if ( isset($params['username']) )
        {
            $userUrl = $userService->getUserUrlForUsername(trim($params['username']));
        }
        
        $displayName = isset($params['name']) ? trim($params['name']) : (isset($params['username']) ? trim($params['username']) : '');
    }

    $markup = "<a href=\"{$userUrl}\">{$displayName}</a>";
    
    return $markup;
}