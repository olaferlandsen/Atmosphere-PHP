<?php
function smarty_modifier_url_args ($url) {
    $args = array_slice(func_get_args(),1);
    $args = array_merge($_GET, $args);
    $args = http_build_query($args);
    return preg_replace("/\?+$/", "", $url) . "?" . $args;
}