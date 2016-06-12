<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: function.thumb.php
* Type: function
* Name: thumbnail
* -------------------------------------------------------------
*/
function smarty_modifier_profilepreview( $data )
{
    
    $attrs = array(
        'data-trigger="hover"',
        'data-container="body"',
        'data-placement="auto"',
        'data-toggle="popover-user"',
        'title="'.htmlentities($data["fullname"], ENT_COMPAT, 'UTF-8', true).'"',
    );
    $allow = array(
        "fullname",
        "verified",
        "images",
        "counters",
    );
    
    foreach ($data AS $key => $value) {
        
        if (!in_array($key, $allow)) {
            continue;
        } elseif ($key == "images") {
            $attrs[] = 'data-img-profile="'.smarty_modifier_thumbnail($value["profile"], 62,62).'"';
            $attrs[] = 'data-poster="'.smarty_modifier_thumbnail($value["poster"],348,134).'"';
        } elseif ($key == "counters") {
            $attrs[] = 'data-followers="'.$value["followers"].'"';
            $attrs[] = 'data-posts="'.$value["posts"].'"';
            $attrs[] = 'data-articles="'.$data["counter_articles"].'"';
        } else {
            $attrs[] = 'data-'.$key.'="'.$value.'"';
        }
    }
    return join(" ",$attrs);
}