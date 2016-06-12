<?php
function smarty_modifier_autobr( $string , $is_xhtml = true, $max_continuous_new_lines = 2, $remove_start_and_finish_new_lines = true)
{
    if (!is_integer($max_continuous_new_lines)) {
        $max_continuous_new_lines = 2;
    }
    $string = preg_replace(
        '/(\n(\r)?){'.$max_continuous_new_lines.',}/i',
        str_repeat("\n", $max_continuous_new_lines),
        $string
    );
    if ($remove_start_and_finish_new_lines === true) {
        $string = preg_replace(
            '/^((\r)?\n(\r)?){1,}/i',
            "",
            $string
        );
        
        $string = preg_replace(
            '/((\r)?\n(\r)?){1,}$/i',
            "",
            $string
        );
    }
    
    $string = nl2br($string);
    return $string;
}