<?php

function smarty_function_text($params, $smarty)
{
    $key = $params['key'];
    unset($params['key']);

    $key = explode('+', $key);

    return OW::getLanguage()->text($key[0], $key[1], $params);
}
