<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: function.thumb.php
* Type: function
* Name: thumb
* -------------------------------------------------------------
*/
function smarty_modifier_hipri( $source , $print = true)
{
	if( $print )
	{
		highlight_string( print_r( $source , true ) );
	}else{
		return highlight_string( print_r( $source , true ) , true);
	}
	
}
?>