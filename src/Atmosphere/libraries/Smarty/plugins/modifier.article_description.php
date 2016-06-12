<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: function.article_description.php
* Type: function
* Name: thumbnail
* -------------------------------------------------------------
*/
function smarty_modifier_article_description( $description ) {
    $description = htmlentities (
        $description,
        ENT_COMPAT,
        "UTF-8",
        true
    );
    $description = preg_replace("/[\r\n]{3,}/i", "\n\n", $description);
    $description = nl2br($description);
    return $description;
}
