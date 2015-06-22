<?php

function smarty_function_text($params, $smarty)
{
    // Reserved params
    $keys = array(
        "key", "escape"
    );
    
    list($prefix, $key) = explode('+', $params["key"]);
    $out = OW::getLanguage()->text($prefix, $key, array_diff_key($params, array_flip($keys)));
    
    if ( isset($params["escape"]) )
    {
        // Load built in smarty modifier
        $smarty->loadPlugin("smarty_modifier_escape");
        
        // Call built in smarty modifier 
        $out = smarty_modifier_escape($out, $params["escape"]);
    }
                
    return $out;
}