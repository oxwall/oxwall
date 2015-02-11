<?php

function smarty_function_account_type_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getAccountTypeLang( trim($params['name']) );
}

?>
