<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: function.download.php
* Type: function
* Name: thumbnail
* -------------------------------------------------------------
*/
function smarty_modifier_download( $source )
{
    return "/cloud/".sha1($source).".do";
    return $source;
}