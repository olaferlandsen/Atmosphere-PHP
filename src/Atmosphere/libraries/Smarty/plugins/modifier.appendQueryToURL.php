<?php

function smarty_modifier_appendQueryToURL( $key , $value = null)
{
    
    $host  = parse_url($_SERVER["SCRIPT_URI"], PHP_URL_HOST);
    $path  = parse_url($_SERVER["SCRIPT_URI"], PHP_URL_PATH);
    $schema  = parse_url($_SERVER["SCRIPT_URI"], PHP_URL_SCHEME);
    
    
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY ),$query);
    
    $query[$key] = $value;
    $query = http_build_query ($query);

    if (!empty($query)) {
        return $schema."://".$host.$path."?".$query;
    }
    return $schema."://".$host.$path;
    
    
    
}